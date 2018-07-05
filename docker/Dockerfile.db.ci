FROM orchardup/mysql

RUN echo Europe/Berlin > /etc/timezone && dpkg-reconfigure --frontend noninteractive tzdata

COPY docker/conf/db/relaxed.cnf /etc/mysql/my.cnf
RUN chmod 644 /etc/mysql/my.cnf

COPY docker/conf/db/run-tmpfs /mysql/run-tmpfs
CMD ["/mysql/run-tmpfs"]

COPY migrations /app/migrations
