import { Router } from 'express';
import { Konverzacija } from '../models/Konverzacija.js';
import { Poruka } from '../models/Poruka.js';
import { getNextSequence } from '../utils/counter.js';
import {
  authenticate,
  enrichParticipants,
  fetchKorisnici,
  formatChat,
  isSuspended,
} from '../middleware/auth.js';

const router = Router();
const ADMIN_ROLE_ID = 1;

router.get('/stats', async (_req, res) => {
  try {
    const [ukupnoChatova, ukupnoPoruka] = await Promise.all([
      Konverzacija.countDocuments(),
      Poruka.countDocuments(),
    ]);
    res.json({ success: true, data: { ukupnoChatova, ukupnoPoruka } });
  } catch (error) {
    res.status(500).json({ success: false, message: error.message });
  }
});

router.get('/chat', async (req, res) => {
  try {
    const filter = {};
    if (req.query.idKorisnik) {
      filter.participants = Number(req.query.idKorisnik);
    }
    const konverzacije = await Konverzacija.find(filter).sort({ created_at: -1 });
    const data = await Promise.all(
      konverzacije.map(async (k) => formatChat(k, await enrichParticipants(k.participants)))
    );
    res.json({ success: true, data });
  } catch (error) {
    res.status(500).json({ success: false, message: error.message });
  }
});

router.get('/chat/:id', async (req, res) => {
  try {
    const konverzacija = await Konverzacija.findOne({ idChat: Number(req.params.id) });
    if (!konverzacija) {
      return res.status(404).json({ success: false, message: 'Chat nije pronađen' });
    }
    const korisnici = await enrichParticipants(konverzacija.participants);
    res.json({ success: true, data: formatChat(konverzacija, korisnici) });
  } catch (error) {
    res.status(500).json({ success: false, message: error.message });
  }
});

router.post('/chat', authenticate, async (req, res) => {
  try {
    if (isSuspended(req.user)) {
      return res.status(403).json({
        success: false,
        message: 'Niste u mogućnosti da kreirate chat dok ste suspendovani',
      });
    }

    const participantIds = [...new Set((req.body.idKorisnici || []).map(Number))];
    if (!participantIds.length) {
      return res.status(422).json({ success: false, message: 'Potrebno je izabrati učesnike' });
    }

    const korisnici = await fetchKorisnici(participantIds);
    if (korisnici.some((k) => k.idUloga === ADMIN_ROLE_ID)) {
      return res.status(422).json({
        success: false,
        message: 'Ne možete kreirati chat koji uključuje administratora',
      });
    }

    const idChat = await getNextSequence('idChat');
    const type = participantIds.length > 2 ? 'group' : participantIds.length === 2 ? 'private' : 'group';
    const konverzacija = await Konverzacija.create({
      idChat,
      type,
      name: req.body.name || (type === 'group' ? `Grupni chat #${idChat}` : 'Privatni chat'),
      participants: participantIds,
      created_at: new Date(),
    });

    const enriched = await enrichParticipants(konverzacija.participants);
    res.status(201).json({
      success: true,
      message: 'Chat uspešno kreiran',
      data: formatChat(konverzacija, enriched),
    });
  } catch (error) {
    res.status(500).json({ success: false, message: error.message });
  }
});

router.delete('/chat/:id', authenticate, async (req, res) => {
  try {
    const konverzacija = await Konverzacija.findOne({ idChat: Number(req.params.id) });
    if (!konverzacija) {
      return res.status(404).json({ success: false, message: 'Chat nije pronađen' });
    }
    if (!konverzacija.participants.includes(req.user.idKorisnik)) {
      return res.status(403).json({ success: false, message: 'Nemate dozvolu za brisanje chata' });
    }
    await Poruka.deleteMany({ idChat: konverzacija.idChat });
    await konverzacija.deleteOne();
    res.json({ success: true, message: 'Chat uspešno obrisan' });
  } catch (error) {
    res.status(500).json({ success: false, message: error.message });
  }
});

export default router;
