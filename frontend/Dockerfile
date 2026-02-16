# Frontend (React + Vite) - Chat aplikacija
FROM node:20-alpine

WORKDIR /app

COPY package.json package-lock.json* ./
RUN npm ci

COPY . .

# Vite dev server na 0.0.0.0 da bi bio dostupan izvan kontejnera
EXPOSE 3000

CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0"]
