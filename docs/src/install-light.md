# foodsharing light and API

> **Note** **these are not being actively developed right now**
> If you are new to slack you can jump right in or read some [introduction](https://get.slack.help/hc/en-us/articles/115004071768) first.

If you want to include the new Django API and the foodsharing light frontend, then:

```
# you may have "api" and "light" directories already present, if so remove them first
git clone https://github.com/foodsharing-dev/foodsharing-light.git light
git clone https://github.com/foodsharing-dev/foodsharing-django-api.git api
./scripts/start
```

Then visit [localhost:18082](http://localhost:18082) for fs light frontend and
[localhost:18000/docs/](http://localhost:18000/docs/) for the API swagger view.

You can run the foodsharing light frontend tests and run tests on change with:

```
./scripts/docker-compose run light sh -c "xvfb-run npm run test:watch -- --browsers Firefox"
```

You can run the api tests with:

```
./scripts/docker-compose run api env/bin/python manage.py test
```

When you update or change the Django API so that it would need to run `pip-sync` or apply migrations,
this can be done with:

```
./scripts/docker-compose restart api
```
