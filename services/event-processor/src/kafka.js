import { Kafka, logLevel } from 'kafkajs';

export const TOPICS = {
  SUSPEND_REQUEST: 'suspenzija-zahtev',
  SUSPEND_PROCESSING: 'suspenzija-obrada',
  SUSPEND_APPLIED: 'suspenzija-primena',
  MESSAGE_CREATED: 'poruka-kreirana',
  PROCESSING_ERROR: 'greska-pri-obradi',
};

const brokers = (process.env.KAFKA_BROKERS || 'kafka:9092').split(',');

export const kafka = new Kafka({
  clientId: 'event-processor',
  brokers,
  logLevel: logLevel.WARN,
  retry: { initialRetryTime: 300, retries: 10 },
});

export async function ensureTopics() {
  const admin = kafka.admin();
  await admin.connect();
  const existing = await admin.listTopics();
  const required = Object.values(TOPICS);
  const missing = required.filter((t) => !existing.includes(t));
  if (missing.length) {
    await admin.createTopics({
      topics: missing.map((topic) => ({ topic, numPartitions: 1, replicationFactor: 1 })),
    });
    console.log('Created topics:', missing.join(', '));
  }
  await admin.disconnect();
}

export function createProducer() {
  return kafka.producer({ allowAutoTopicCreation: false });
}

export function createConsumer(groupId) {
  return kafka.consumer({ groupId });
}

export async function publish(producer, topic, payload) {
  await producer.send({
    topic,
    messages: [
      {
        key: String(payload.correlationId || payload.korisnikId || Date.now()),
        value: JSON.stringify({ ...payload, timestamp: new Date().toISOString() }),
      },
    ],
  });
}
