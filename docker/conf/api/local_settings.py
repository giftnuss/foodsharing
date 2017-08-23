DEBUG = True

# SECURITY WARNING: keep the secret key used in production secret!
SECRET_KEY = '^!+w4(8^9alg-s5b9(6@k!9#yk@8d8rgvd&7h=901p8xfb($w8'

DATABASES = {
    'default': {
        'ENGINE': 'django.db.backends.mysql',
        'NAME': 'foodsharing',
        'USER': 'root',
        'PASSWORD': 'root',
        'HOST': 'db',
        'PORT': '3306',
    }
}

ALLOWED_HOSTS = ['*']
