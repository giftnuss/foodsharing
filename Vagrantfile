unless Vagrant.has_plugin?("vagrant-docker-compose")
  system("vagrant plugin install vagrant-docker-compose")
  puts "Dependencies installed, please try the command again."
  exit
end

ports = [
  18080, # main website
  18081, # phpmyadmin
  18083, # maildev

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

   
