import express from 'express';
import client from 'prom-client';
import {
  TOPICS,
  createConsumer,
  createProducer,
  ensureTopics,
  publish,
} from './kafka.js';

const PORT = process.env.PORT || 8010;
const register = new client.Registry();
client.collectDefaultMetrics({ register });
const eventsProcessed = new client.Counter({
  name: 'event_processor_events_total',
  help: 'Processed Kafka events',
  labelNames: ['topic', 'status'],
  registers: [register],
});
const kafkaMessages = new client.Counter({
  name: 'event_processor_kafka_messages_total',
  help: 'Kafka messages consumed/produced',
  labelNames: ['direction', 'topic'],
  registers: [register],
});

const app = express();
app.use(express.json());

app.get('/health', (_req, res) => res.json({ status: 'ok', service: 'event-processor' }));
app.get('/metrics', async (_req, res) => {
  res.set('Content-Type', register.contentType);
  res.end(await register.metrics());
});

/**
 * Hybrid Processor (Consumer + Producer):
 * 1. Consumes suspenzija-zahtev
 * 2. Validates saga step
 * 3. Publishes suspenzija-obrada or greska-pri-obradi
 */
async function processSuspendRequest(message, producer) {
  const payload = JSON.parse(message.value.toString());
  const { korisnikId, days, sankcijaId, prijavaId, correlationId } = payload;

  if (!korisnikId || !days) {
    await publish(producer, TOPICS.PROCESSING_ERROR, {
      correlationId,
      source: 'event-processor',
      error: 'Nedostaju obavezna polja (korisnikId, days)',
      originalTopic: TOPICS.SUSPEND_REQUEST,
      payload,
    });
    eventsProcessed.inc({ topic: TOPICS.SUSPEND_REQUEST, status: 'error' });
    return;
  }

  await publish(producer, TOPICS.SUSPEND_PROCESSING, {
    correlationId: correlationId || `saga-${sankcijaId || Date.now()}`,
    korisnikId,
    days,
    sankcijaId,
    prijavaId,
    validatedBy: 'event-processor',
  });
  kafkaMessages.inc({ direction: 'produced', topic: TOPICS.SUSPEND_PROCESSING });
  eventsProcessed.inc({ topic: TOPICS.SUSPEND_REQUEST, status: 'success' });
}

async function startKafka(producer) {
  const consumer = createConsumer('event-processor-group');
  await consumer.connect();
  await consumer.subscribe({ topic: TOPICS.SUSPEND_REQUEST, fromBeginning: false });

  await consumer.run({
    eachMessage: async ({ topic, message }) => {
      kafkaMessages.inc({ direction: 'consumed', topic });
      if (topic === TOPICS.SUSPEND_REQUEST) {
        await processSuspendRequest(message, producer);
      }
    },
  });
  console.log(`Listening on topic: ${TOPICS.SUSPEND_REQUEST}`);
}

async function main() {
  await ensureTopics();
  const producer = createProducer();
  await producer.connect();

  await startKafka(producer);

  app.listen(PORT, '0.0.0.0', () => {
    console.log(`Event processor on :${PORT} (hybrid consumer/producer)`);
  });
}

main().catch((err) => {
  console.error(err);
  process.exit(1);
});
