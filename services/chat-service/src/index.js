import express from 'express';
import cors from 'cors';
import mongoose from 'mongoose';
import client from 'prom-client';
import chatRoutes from './routes/chat.js';
import porukaRoutes from './routes/poruka.js';
import { startSuspendConsumer } from './messaging/kafka.js';

const app = express();
const PORT = process.env.PORT || 8003;
const MONGODB_URI = process.env.MONGODB_URI || 'mongodb://localhost:27017/chat_db';
const ALLOWED_ORIGINS = (process.env.CORS_ORIGINS || 'http://localhost:3000').split(',');

const register = new client.Registry();
client.collectDefaultMetrics({ register });
const httpRequests = new client.Counter({
  name: 'chat_http_requests_total',
  help: 'HTTP requests',
  labelNames: ['method', 'route', 'status'],
  registers: [register],
});

app.use(
  cors({
    origin(origin, callback) {
      if (!origin || ALLOWED_ORIGINS.includes(origin)) {
        callback(null, true);
      } else {
        callback(new Error('CORS blocked'));
      }
    },
    credentials: true,
  })
);
app.use(express.json({ limit: '1mb' }));

app.use((req, res, next) => {
  res.on('finish', () => {
    httpRequests.inc({ method: req.method, route: req.path, status: res.statusCode });
  });
  next();
});

app.get('/health', (_req, res) => res.json({ status: 'ok', service: 'chat-service' }));
app.get('/metrics', async (_req, res) => {
  res.set('Content-Type', register.contentType);
  res.end(await register.metrics());
});

app.use('/api', chatRoutes);
app.use('/api', porukaRoutes);

async function start() {
  try {
    await mongoose.connect(MONGODB_URI);
    console.log('Connected to MongoDB');

    app.listen(PORT, '0.0.0.0', () => {
      console.log(`Chat service running on port ${PORT}`);
    });

    if (process.env.KAFKA_ENABLED !== 'false') {
      startSuspendConsumer()
        .then(() => console.log('Kafka consumer started (suspenzija-primena)'))
        .catch((err) => console.error('Kafka consumer unavailable:', err.message));
    }
  } catch (error) {
    console.error('Failed to start chat service:', error);
    process.exit(1);
  }
}

start();
