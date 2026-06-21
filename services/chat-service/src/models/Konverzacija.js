import mongoose from 'mongoose';

const konverzacijaSchema = new mongoose.Schema({
  idChat: { type: Number, unique: true, required: true },
  type: { type: String, enum: ['group', 'private'], default: 'group' },
  name: { type: String, default: '' },
  participants: { type: [Number], default: [] },
  created_at: { type: Date, default: Date.now },
});

konverzacijaSchema.set('toJSON', { virtuals: true });
konverzacijaSchema.set('toObject', { virtuals: true });

export const Konverzacija = mongoose.model('Konverzacija', konverzacijaSchema, 'konverzacije');
