import express from 'express';
import cors from 'cors';
import mongoose from 'mongoose';
import chatRoutes from './routes/chat.js';
import porukaRoutes from './routes/poruka.js';

const app = express();
const PORT = process.env.PORT || 8003;
const MONGODB_URI = process.env.MONGODB_URI || 'mongodb://localhost:27017/chat_db';

app.use(cors());
app.use(express.json());

app.get('/health', (_req, res) => res.json({ status: 'ok', service: 'chat-service' }));

app.use('/api', chatRoutes);
app.use('/api', porukaRoutes);

async function start() {
  try {
    await mongoose.connect(MONGODB_URI);
    console.log('Connected to MongoDB');
    app.listen(PORT, '0.0.0.0', () => {
      console.log(`Chat service running on port ${PORT}`);
    });
  } catch (error) {
    console.error('Failed to start chat service:', error);
    process.exit(1);
  }
}

start();
