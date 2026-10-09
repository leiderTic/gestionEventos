FROM node:20-alpine

WORKDIR /app

COPY package*.json ./

RUN npm install

COPY . .

EXPOSE 5174

# Vite necesita --host 0.0.0.0 para ser accesible fuera del contenedor
CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0", "--port", "5174"]
