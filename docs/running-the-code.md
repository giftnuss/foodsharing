# Running the code
(Unten gibt es eine deutsche Übersetzung)

We use Docker and Docker Compose.

You can use the Docker Compose setup if you are using one of the following:

- Linux (64bit)
- OSX Yosemite 10.10.3 or higher
- Windows 10 Pro or higher

If you are not using one of those, then try the Vagrant + Docker Compose setup.

## Linux

Install
[Docker CE](https://docs.docker.com/engine/installation/).

And make sure you have 
[Docker Compose](https://docs.docker.com/compose/install/) (at least version 1.6.0)
installed too (often comes with Docker).

### Fedora 31 
Switched to using cgroupsV2 by default, which is not yet supported from docker.

To disable v2 cgroups, run: 

```
sudo grubby --update-kernel=ALL --args="systemd.unified_cgroup_hierarchy=0"
```
And restart your machine.

### Fedora 32 or Debian 10
Some distributions use as default firewall backend nftables. Docker currently only supports iptables. The containers cannot establish network connections with each other.

**Workaround:** You have to set the entry `FirewallBackend = iptables` in /etc/firewalld/firewalld.conf. 

After a restart the services firewalld and docker it should work.

If you cannot connect to Docker with your local user, then you may want to add yourself to the Docker group:

### Fedora 32

Download docker from: https://download.docker.com/linux/fedora/31/x86_64/stable/Packages/
```
sudo dnf -y install /path/to/package.rpm
sudo systemctl start docker
```
> Error response from daemon: cgroups: cgroup mountpoint does not exist: unknown

```
sudo mkdir /sys/fs/cgroup/systemd
sudo mount -t cgroup -o none,name=systemd cgroup /sys/fs/cgroup/systemd
```
# General on Linux
```
sudo usermod -aG docker $USER
```
Then either log in again to reload the groups or run (for each shell...)
`su - $USER`

Should now be able to connect without errors

`docker info`

Then:

```
git clone git@gitlab.com:foodsharing-dev/foodsharing.git foodsharing
cd foodsharing
./scripts/start
```

## OSX Yosemite 10.10.3 or higher

Install [Docker for Mac](https://docs.docker.com/docker-for-mac/install/) ([direct link](https://download.docker.com/mac/stable/Docker.dmg)).

Then:

```
git clone git@gitlab.com:foodsharing-dev/foodsharing.git foodsharing
cd foodsharing
./scripts/start
```

## Windows 

### New approach on WSL2

* Install Ubuntu 20.xx via Windows AppStore (WSL2) (you need to start it once for step 3)
* Install windowsTerminal via Appstore
* Open a new tab in WinTerminal with Ubuntu
* Change to your target directory
* Execute git download (you may need to add an ssh key from this environment)
```
`git clone git@gitlab.com:foodsharing-dev/foodsharing.git foodsharing
```

* Add the following to ~/.bashrc with editor (e.g. nano)
```
export DOCKER_HOST=tcp://localhost:2375
export DOCKER_BUILDKIT=1
```
* Check in docker settings - Resources - WSL Integration your environment is active
```
cd /foodsharing
sudo./scripts/start
sudo./scripts/seed
```

## On WSL1

If you are using Windows 10 Pro or higher go with this link:

Install [Docker for Windows](https://docs.docker.com/docker-for-windows/install/) ([direct link](https://download.docker.com/win/stable/Docker%20Desktop%20Installer.exe)) and
[Git for Windows](https://git-scm.com/download/win).

If you are using Windows 10 Home, make sure you fulfill all [system requirements](https://docs.docker.com/docker-for-windows/install-windows-home/#system-requirements)
and then install both [Docker Desktop on Windows Home](https://docs.docker.com/docker-for-windows/install-windows-home/) and [Git for Windows](https://git-scm.com/download/win). 

It is important to grant docker access to C: (in the graphical docker interface: settings -> resources -> filesharing -> mark C, apply and restart)

You can test your docker in the command shell (e.g. cmd or powershell) with the command ```docker --version```. If it shows something, you're good to go.

Restart your Windows now.

There is a graphical user interface to administrate the repo, which is recommended for Git beginners.

But you can use the Git Bash shell just like in Linux to clone it:

```
git clone git@gitlab.com:foodsharing-dev/foodsharing.git foodsharing
```

After this command, your files will be found in the folder ```%UserProfile%\foodsharing```

To start the containers, use the Git Bash shell:
```
cd foodsharing
./scripts/start
```

The first time you run the start script, which takes a lot of time, you probably have to give the windows firewall the OK to let Docker work. 

### Known Issues on Windows

 - general

If something is wrong, please check in your task manager under "performance" if the virtualisation is activated and troubleshoot if necessary.

 - git trouble (on WSL1)
 
 If git does not working well, please do:
 ```
 cd foodsharing/bin
 tr -d '\15' < console > console
``` 
Make sure not to commit the `console` file and maybe discuss further steps with the team. 
 
 - ```[RuntimeException]```

If you get a ```[RuntimeException]```, let ```./scripts/start``` run again and again and maybe even again until it's done.

 - yarn lint

There is a known bug concerning yarn, see: https://github.com/yarnpkg/yarn/issues/7187 and https://github.com/yarnpkg/yarn/issues/7732 and https://github.com/yarnpkg/yarn/issues/7551

 - Changes in js, vue etc. aren't showing up

In order to have the webpack-dev-server recognize changes you have to add this watchOptions block to ```client/serve.config.js```
```
[...]
module.exports = {
  [...]
  devServer: {
    watchOptions: {
      poll: true
    },
    [...]
```

Note: Please make sure not to commit this file afterwards with your changes.

## Vagrant

If you cannot use any of the above methods, then this should work with every common operation system.

However, we are less familiar with this solution, so we may be less able to support you.

Install
[VirtualBox](https://www.virtualbox.org/wiki/Downloads) and
[Vagrant](https://www.vagrantup.com/downloads.html).

Then:

```
git clone git@gitlab.com:foodsharing-dev/foodsharing.git foodsharing
cd foodsharing
vagrant up
```

### Daily work

`vagrant up` starts the machine and foodsharing project.

`vagrant halt` stops the virtual machine.

`vagrant ssh` connects to the virtual machine.

Once connected to the virtual machine, go to /vagrant with `cd /vagrant`.
This is where the foodsharing folder is mounted in the VM.
From there on, you can run all scripts with `./scripts/NAME`.

Note:
`./scripts/start` will always be executed, when you start the virtual machine with `vagrant up`.

There is a known bug when running VirtualBox + nginx that nginx serves files from a memory cache. If you encounter this problem, then it can probably be fixed by emptying the memory cache with ``sync; sudo sh -c "/bin/echo 3 > /proc/sys/vm/drop_caches"`` or even running this every second with ``watch -n 1 'sync; sudo sh -c "/bin/echo 3 > /proc/sys/vm/drop_caches"'``.


------

# Den Code zum laufen bringen

Wir benutzen Docker und Docker Compose.

Du kannst das Docker Compose setup benutzen, falls du mit 

- Linux (64bit)
- OSX Yosemite 10.10.3 oder darüber - oder aber
- Windows 10 Pro oder darüber arbeitest.


Falls du keines von denen nutzt, probiere es mit Vagrant + dem Docker Compose Setup.

## Linux

Installiere
[Docker CE](https://docs.docker.com/engine/installation/).

Stelle sicher, dass du
[Docker Compose](https://docs.docker.com/compose/install/) (mindestens version 1.6.0)
auch installiert hast (gibt es oft zusammen mit Docker).

### Fedora 31 
Fedora benutzt mittlerweile cgroupsV2 als Standart, was noch nicht von Docker unterstützt wird. Um es abzustellen, lautet der Befehl:

```
sudo grubby --update-kernel=ALL --args="systemd.unified_cgroup_hierarchy=0"
```
Danach neu starten.

### Fedora 32 oder Debian 10
Manchmal wird als Standard  firewall backend nftables genutzt. Docker unterstützt derzeit nur iptables. Die Docker-Container können dann keine Netzwerkverbindung miteinander herstellen.

**Hilfestellung:** Setze den Eintrag `FirewallBackend = iptables` in /etc/firewalld/firewalld.conf. 

Nach einem Neustart sollten firewalld und Docker arbeiten.

Wenn du dich als lokaler Nutzer nicht mit Docker verbinden kannst, kann es helfen, dich selbst zur Docker Gruppe hinzuzufügen:

### Fedora 32

Lade docker hier herunter: https://download.docker.com/linux/fedora/31/x86_64/stable/Packages/
```
sudo dnf -y install /path/to/package.rpm
sudo systemctl start docker
```
> Error response from daemon: cgroups: cgroup mountpoint does not exist: unknown

```
sudo mkdir /sys/fs/cgroup/systemd
sudo mount -t cgroup -o none,name=systemd cgroup /sys/fs/cgroup/systemd
```
# General on Linux
```
sudo usermod -aG docker $USER
```
Dann logge dich entweder noch einmal ein oder lade die Gruppen neu oder führe (für jedes Fenster) aus:
`su - $USER`

Jetzt solltest du dich ohne Fehler verbinden können.

`docker info`

Dann:

```
git clone git@gitlab.com:foodsharing-dev/foodsharing.git foodsharing
cd foodsharing
./scripts/start
```

## OSX Yosemite 10.10.3 oder darüber

Installiere [Docker for Mac](https://docs.docker.com/docker-for-mac/install/) ([direct link](https://download.docker.com/mac/stable/Docker.dmg)).

Dann:

```
git clone git@gitlab.com:foodsharing-dev/foodsharing.git foodsharing
cd foodsharing
./scripts/start
```

## Windows 

### Neuer Zugang bei WSL2

* Installiere Ubuntu 20.xx via Windows AppStore (WSL2) (das musst du für Schritt drei einmal starten)
* Installiere windowsTerminal via Appstore
* Öffne einen neuen Tab im WinTerminal mit Ubuntu
* Wechsle dein Zielverzeichnis
* Führe aus git download aus (es kann sein, dass du einen SSH-Schlüssel für diese Umgebung hinzufügen musst)
```
`git clone git@gitlab.com:foodsharing-dev/foodsharing.git foodsharing
```

* Füge das Folgende ~/.bashrc mit einem Editor hinzu (z.B. nano)
```
export DOCKER_HOST=tcp://localhost:2375
export DOCKER_BUILDKIT=1
```
* Prüfe in den Docker Einstellungen - Resources - WSL Integration, dass deine Umgebung aktiv ist.
```
cd /foodsharing
sudo./scripts/start
sudo./scripts/seed
```

## Bei WSL1

Wenn du Windows 10 pro oder darüber nutzt, klick auf diesen Link:

Installiere [Docker for Windows](https://docs.docker.com/docker-for-windows/install/) ([Direktlink](https://download.docker.com/win/stable/Docker%20Desktop%20Installer.exe)) und
[Git for Windows](https://git-scm.com/download/win).

Wenn Du Windows 10 Home verwendest, stelle sicher, dass Du alle [Systemanforderungen](https://docs.docker.com/docker-for-windows/install-windows-home/#system-requirements) erfüllst.  Dann installiere sowohl [Docker Desktop on Windows Home](https://docs.docker.com/docker-for-windows/install-windows-home/) und [Git for Windows](https://git-scm.com/download/win). 

Es ist wichtig, Docker-Zugriff auf C: zu gewähren (in der grafischen Docker-Oberfläche: Einstellungen -> Ressourcen -> Filesharing -> C markieren, anwenden und neu starten).

Du kannst deinen in der Kommando-Shell (z.B. cmd oder powershell) mit dem Befehl ```docker --version``` testen. Wenn es etwas anzeigt, kannst du loslegen.

Starte jetzt dein Windows neu.

Es gibt eine grafische Benutzeroberfläche zur Verwaltung des Repos, die für Git-Anfänger empfohlen wird. Aber Du kannst die Git Bash-Shell genau wie unter Linux benutzen, um sie zu klonen:

```
git clone git@gitlab.com:foodsharing-dev/foodsharing.git foodsharing
```

Nach diesem Befehl befinden sich Deine Dateien im Ordner ````%UserProfile%\foodsharing```

Um die Container zu starten, verwende die Git Bash-Shell:
```
cd foodsharing
./scripts/start
```

Wenn Du das Startskript zum ersten Mal ausführst, was sehr viel Zeit in Anspruch nimmt, musst du wahrscheinlich der Windows-Firewall das OK geben, damit Docker funktioniert. 

### Bekannte Windows-Fehler

 - allgemein

Wenn etwas nicht in Ordnung ist, überprüfe bitte in Deinem Task-Manager unter "Leistung", ob die Virtualisierung aktiviert ist und behebe gegebenenfalls Fehler.

 - git Fehler (bei WSL1)
 
 Wenn git nicht gut arbeitet, mach bitte folgendes:
 ```
 cd foodsharing/bin
 tr -d '\15' < console > console
``` 
Stell sicher, dass du die `Konsole'-Datei nicht committest und besprich vielleicht weitere Schritte mit dem Team. 
 
 - ```[RuntimeException]```

Wenn du eine ```[RuntimeException]```, bekommst, lass ```./scripts/start``` noch einmal und wieder und wieder laufen, bis alles fertig ist.

 - yarn lint

Es gibt einen bekannten Fehler bezüglich yarn, siehe: https://github.com/yarnpkg/yarn/issues/7187 and https://github.com/yarnpkg/yarn/issues/7732 und https://github.com/yarnpkg/yarn/issues/7551

 - Veränderungen in js, vue etc. erscheinen nicht

Damit der webpack-dev-server Änderungen erkennt, musst du diesen watchOptions-Block zu ```client/serve.config.js``` hinzufügen:
```
[...]
module.exports = {
  [...]
  devServer: {
    watchOptions: {
      poll: true
    },
    [...]
```

Hinweis: Bitte achte darauf, diese Datei nicht nachträglich mit Ihren Änderungen zu übertragen.

## Vagrant

Wenn Du keine der oben genannten Methoden verwenden kannst, dann sollte dies mit jedem gängigen Betriebssystem funktionieren. Allerdings sind wir mit dieser Lösung weniger vertraut, so dass wir Dich damit möglicherweise weniger gut unterstützen können:

Installiere
[VirtualBox](https://www.virtualbox.org/wiki/Downloads) und
[Vagrant](https://www.vagrantup.com/downloads.html).

Dann:

```
git clone git@gitlab.com:foodsharing-dev/foodsharing.git foodsharing
cd foodsharing
vagrant up
```

### Für's Tägliche Arbeiten
`vagrant up` startet die Maschine und das Foodsharing-Projekt.

`vagrant halt` stoppt die virtuelle Maschine.

`vagrant ssh` verbindet sich mit der virtuellen Maschine.

Sobald die Verbindung mit der virtuellen Maschine hergestellt ist, gehe mit `cd /vagrant` nach /vagrant.
Dort wird der Foodsharing-Ordner in der VM gemountet.
Von dort aus kannst Du alle Skripte mit `./scripts/NAME` ausführen.

Notiz:
`./Skripte/Start` wird immer ausgeführt, wenn Du die virtuelle Maschine mit `vagrant up` startest.

Es gibt einen bekannten Fehler beim Ausführen von VirtualBox + nginx, dass nginx Dateien aus einem Speicher-Cache bedient. Wenn Du auf dieses Problem stößt, dann kann es wahrscheinlich behoben werden, indem Du den Speicher-Cache mit
``sync; sudo sh -c "/bin/echo 3 > /proc/sys/vm/drop_caches"`` or even running this every second with ``watch -n 1 'sync; sudo sh -c "/bin/echo 3 > /proc/sys/vm/drop_caches"'`` ausführst.
