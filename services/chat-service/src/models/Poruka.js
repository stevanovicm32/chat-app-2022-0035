import mongoose from 'mongoose';

const porukaSchema = new mongoose.Schema({
  idPoruka: { type: Number, unique: true, required: true },
  conversation_id: { type: mongoose.Schema.Types.ObjectId, ref: 'Konverzacija', required: true },
  idChat: { type: Number, required: true, index: true },
  sender_id: { type: Number, required: true },
  content: {
    text: { type: String, required: true },
    attachment_url: { type: String, default: null },
    file_type: { type: String, default: null },
  },
  timestamp: { type: Date, default: Date.now },
  read_by: { type: [Number], default: [] },
});

export const Poruka = mongoose.model('Poruka', porukaSchema, 'poruke');
