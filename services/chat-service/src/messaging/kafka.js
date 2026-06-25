import { Kafka, logLevel } from 'kafkajs';

export const TOPICS = {
  MESSAGE_CREATED: 'poruka-kreirana',
  SUSPEND_APPLIED: 'suspenzija-primena',
};

const brokers = (process.env.KAFKA_BROKERS || 'kafka:9092').split(',');

export const kafka = new Kafka({
  clientId: 'chat-service',
  brokers,
  logLevel: logLevel.WARN,
});

let producer;

export async function getProducer() {
  if (!producer) {
    producer = kafka.producer();
    await producer.connect();
  }
  return producer;
}

export async function publishMessageCreated(payload) {
  const p = await getProducer();
  await p.send({
    topic: TOPICS.MESSAGE_CREATED,
    messages: [{ key: String(payload.porukaId), value: JSON.stringify(payload) }],
  });
}

const suspendedUsers = new Map();

export function isUserSuspendedCached(userId) {
  const until = suspendedUsers.get(Number(userId));
  if (!until) return false;
  if (new Date(until) < new Date()) {
    suspendedUsers.delete(Number(userId));
    return false;
  }
  return true;
}

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
  }
  await admin.disconnect();
}

export async function startSuspendConsumer() {
  await ensureTopics();
  const consumer = kafka.consumer({ groupId: 'chat-service-group' });
  await consumer.connect();
  await consumer.subscribe({ topic: TOPICS.SUSPEND_APPLIED, fromBeginning: false });
  await consumer.run({
    eachMessage: async ({ message }) => {
      const payload = JSON.parse(message.value.toString());
      if (payload.korisnikId && payload.suspendovan) {
        suspendedUsers.set(Number(payload.korisnikId), payload.suspendovan);
        console.log(`Cached suspension for user ${payload.korisnikId}`);
      }
    },
  });
}
