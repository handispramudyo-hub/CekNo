# ---------------------------------------------------------------
# Stage 1: build frontend SPA ke dist
# ---------------------------------------------------------------
FROM node:22-alpine AS frontend

WORKDIR /app

COPY frontend/package.json frontend/package-lock.json ./
RUN npm ci

COPY frontend/ .
RUN npm run build

# ---------------------------------------------------------------
# Stage 2: nginx runtime dengan dist + config
# ---------------------------------------------------------------
FROM nginx:1.25-alpine

COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY --from=frontend /app/dist /usr/share/nginx/html

EXPOSE 80