required_plugins = %w(vagrant-docker-compose)
required_plugins.each do |plugin|
    exec "vagrant plugin install #{plugin}; vagrant #{ARGV.join(" ")}" unless Vagrant.has_plugin? plugin || ARGV[0] == 'plugin'
end

ports = [
  18080, # main website
  18081, # phpmyadmin
  18084, # maildev. (changed, since 18083 is used by virtualbox (vboxwebsrv))


  # these two are not available for a default setup
  # check the README for instructions on setting them
  # if you want, they are optional
  18082, # foodsharing light
  18000  # django api
]

Vagrant.configure("2") do |config|
  
  config.vm.box = "ubuntu/trusty64"

  ports.each do |port|
    config.vm.network(:forwarded_port, guest: port, host: port)
  end

  config.vm.provision :shell, inline: "apt-get update"
  
  config.vm.provision :docker
  config.vm.provision :docker_compose
  
  config.vm.provision "shell",
    inline: "cd /vagrant && ./scripts/start", run: 'always'
    
end

   
