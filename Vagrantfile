unless Vagrant.has_plugin?("vagrant-docker-compose")
  system("vagrant plugin install vagrant-docker-compose")
  puts "Dependencies installed, please try the command again."
  exit
end

Vagrant.configure("2") do |config|
  
  config.vm.box = "ubuntu/trusty64"

  config.vm.network(:forwarded_port, guest: 18080, host: 18080)
  config.vm.network(:forwarded_port, guest: 18081, host: 18081)
  config.vm.network(:forwarded_port, guest: 18082, host: 18082)

  config.vm.provision :shell, inline: "apt-get update"
  
  config.vm.provision :docker
  config.vm.provision :docker_compose
  
  config.vm.provision "shell",
    inline: "cd /vagrant && ./scripts/start", run: 'always'
    
end

   
