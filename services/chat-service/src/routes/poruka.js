import { Router } from 'express';
import { Konverzacija } from '../models/Konverzacija.js';
import { Poruka } from '../models/Poruka.js';
import { getNextSequence } from '../utils/counter.js';
import { authenticate, formatPoruka, isSuspended } from '../middleware/auth.js';

const router = Router();
const MODERATOR_ROLE_ID = 3;

router.get('/poruka', async (req, res) => {
  try {
    const filter = {};
    if (req.query.idChat) filter.idChat = Number(req.query.idChat);
    if (req.query.idKorisnik) filter.sender_id = Number(req.query.idKorisnik);

    const poruke = await Poruka.find(filter).sort({ timestamp: 1 });
    res.json({ success: true, data: poruke.map(formatPoruka) });
  } catch (error) {
    res.status(500).json({ success: false, message: error.message });
  }
});

router.get('/poruka/:id', async (req, res) => {
  try {
    const poruka = await Poruka.findOne({ idPoruka: Number(req.params.id) });
    if (!poruka) {
      return res.status(404).json({ success: false, message: 'Poruka nije pronađena' });
    }
    res.json({ success: true, data: formatPoruka(poruka) });
  } catch (error) {
    res.status(500).json({ success: false, message: error.message });
  }
});

async function createPoruka(req, res) {
  try {
    if (isSuspended(req.user)) {
      return res.status(403).json({
        success: false,
        message: 'Niste u mogućnosti da šaljete poruke dok ste suspendovani',
      });
    }

    const idChat = Number(req.params.chatId || req.body.idChat);
    const konverzacija = await Konverzacija.findOne({ idChat });
    if (!konverzacija) {
      return res.status(404).json({ success: false, message: 'Chat nije pronađen' });
    }

    const tekst = req.body.tekst;
    if (!tekst || !String(tekst).trim()) {
      return res.status(422).json({ success: false, message: 'Tekst poruke je obavezan' });
    }

    const idPoruka = await getNextSequence('idPoruka');
    const poruka = await Poruka.create({
      idPoruka,
      conversation_id: konverzacija._id,
      idChat,
      sender_id: req.user.idKorisnik,
      content: {
        text: String(tekst),
        attachment_url: req.body.attachment_url || null,
        file_type: req.body.file_type || null,
      },
      timestamp: new Date(),
      read_by: [req.user.idKorisnik],
    });

    res.status(201).json({
      success: true,
      message: 'Poruka uspešno kreirana',
      data: formatPoruka(poruka),
    });
  } catch (error) {
    res.status(500).json({ success: false, message: error.message });
  }
}

router.post('/chat/:chatId/poruka', authenticate, createPoruka);

router.post('/poruka', authenticate, (req, res) => createPoruka(req, res));

router.delete('/poruka/:id', authenticate, async (req, res) => {
  try {
    const poruka = await Poruka.findOne({ idPoruka: Number(req.params.id) });
    if (!poruka) {
      return res.status(404).json({ success: false, message: 'Poruka nije pronađena' });
    }

    const isModerator = req.user.idUloga === MODERATOR_ROLE_ID;
    if (!isModerator && poruka.sender_id !== req.user.idKorisnik) {
      return res.status(403).json({ success: false, message: 'Nemate dozvolu da obrišete ovu poruku' });
    }

    await poruka.deleteOne();
    res.json({ success: true, message: 'Poruka uspešno obrisana' });
  } catch (error) {
    res.status(500).json({ success: false, message: error.message });
  }
});

export default router;
