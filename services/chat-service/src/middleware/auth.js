const IDENTITY_URL = process.env.IDENTITY_SERVICE_URL || 'http://identity-service:8001';

export async function authenticate(req, res, next) {
  const auth = req.headers.authorization;
  if (!auth) {
    return res.status(401).json({ success: false, message: 'Niste autentifikovani' });
  }

  try {
    const response = await fetch(`${IDENTITY_URL}/api/user`, {
      headers: { Authorization: auth, Accept: 'application/json' },
    });

    if (!response.ok) {
      return res.status(401).json({ success: false, message: 'Neispravan token' });
    }

    const body = await response.json();
    if (!body.success || !body.data) {
      return res.status(401).json({ success: false, message: 'Neispravan token' });
    }

    req.user = body.data;
    next();
  } catch (error) {
    console.error('Identity service error:', error.message);
    return res.status(503).json({ success: false, message: 'Identity servis nedostupan' });
  }
}

export async function fetchKorisnici(ids = []) {
  const response = await fetch(`${IDENTITY_URL}/api/korisnik`, {
    headers: { Accept: 'application/json' },
  });
  if (!response.ok) return [];
  const body = await response.json();
  const all = body.data || [];
  if (!ids.length) return all;
  return all.filter((k) => ids.includes(k.idKorisnik));
}

export function isSuspended(user) {
  if (!user?.suspendovan) return false;
  return new Date(user.suspendovan) > new Date();
}

export function formatKorisnikStub(id) {
  return { idKorisnik: id, email: `korisnik${id}@ref.local`, uloga: null };
}

export async function enrichParticipants(participantIds) {
  const korisnici = await fetchKorisnici(participantIds);
  const map = new Map(korisnici.map((k) => [k.idKorisnik, k]));
  return participantIds.map((id) => map.get(id) || formatKorisnikStub(id));
}

export function formatChat(konverzacija, korisnici = []) {
  return {
    idChat: konverzacija.idChat,
    type: konverzacija.type,
    name: konverzacija.name || (konverzacija.type === 'private' ? 'Privatni chat' : `Grupni chat #${konverzacija.idChat}`),
    participants: konverzacija.participants,
    korisnici,
    created_at: konverzacija.created_at,
    updated_at: konverzacija.created_at,
    createdAt: konverzacija.created_at,
    updatedAt: konverzacija.created_at,
  };
}

export function formatPoruka(poruka) {
  return {
    idPoruka: poruka.idPoruka,
    tekst: poruka.content?.text || '',
    idChat: poruka.idChat,
    idKorisnik: poruka.sender_id,
    content: poruka.content,
    timestamp: poruka.timestamp,
    read_by: poruka.read_by,
    created_at: poruka.timestamp,
    updated_at: poruka.timestamp,
    datoteke: poruka.content?.attachment_url
      ? [{ url: poruka.content.attachment_url, tip: poruka.content.file_type }]
      : [],
  };
}
