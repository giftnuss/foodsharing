FROM nginx:latest

RUN apt-get update

RUN apt-get install -y curl

COPY app.conf /etc/nginx/conf.d/default.conf
COPY foodsharing.conf /etc/nginx/includes/foodsharing.conf
