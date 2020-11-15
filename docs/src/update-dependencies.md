# Update our software dependencies

## Slack
Every Sunday a schedules pipeline "send outdated dependency report to slack" is started and the result can be read in the channel #fs-outdated.

## Update from backend using Composer

### Manually check the version

```./scripts/composer outdated -D```
Restricts the list of packages to your direct dependencies


The color coding is as such:

    green (=): Dependency is in the latest version and is up to date.
    yellow (~): Dependency has a new version available that includes backwards compatibility breaks according to semver, so upgrade when you can but it may involve work.
    red (!): Dependency has a new version that is semver-compatible and you should upgrade it.

[https://getcomposer.org/doc/03-cli.md#outdated](https://getcomposer.org/doc/03-cli.md#outdated)


#### Structure and explanation of version numbers

* 0.x.x is a beta version. Here every change can contain ``Breaking Changes''.

2.3.5  
│ │ │  
│ │ └───────── Patch (contains mostly bug fixes)  
│ └─────────── Minor version (mostly functional extension)  
└───────────── Major version (mostly significant change)  

### Rules

* Don't mix dev dependencies with dependencies in a commit
* ~ instead of ^ to have similar systems between server and dev computer and to avoid big unwanted changes during yarn update
* only run yarn update if there are only outdated packages with explicit version information
* If you don't know what belongs together, then update only one package per commit.
* Major updates are best done in your own MR with changelog entry.

### Update

- Change of version number in composer.json 

```./scripts/composer update PACKAGENAME* --with-dependencies```

## Update from frontend using yarn

### Manually check the version

* ```./scripts/docker-compose run --rm client sh```
* ```yarn outdated```

### Manually check for security vulnerabilities

* ```./scripts/docker-compose run --rm client sh```
* ```yarn audit```

### Update client

* Change the version number in ```client/package.json``` 
* ```./scripts/docker-compose run --rm client sh```
* ```yarn PACKAGENAME```

### Update chat

* Change the version number in ```chat/package.json```
* ```./scripts/docker-compose run --rm chat sh```
* ```yarn PACKAGENAME```

### Update deployer
* Change the version number in ```deployer/package.json```
* ```./scripts/composer update -d deployer```

### Tests after every single update

* `./scripts/lint` for javascript and php
