pathOfFoodsharing = "foodsharing"

unless Vagrant.has_plugin?("vagrant-docker-compose")
  system("vagrant plugin install vagrant-docker-compose")
  puts "Dependencies installed, please try the command again."
  exit
end

Vagrant.configure("2") do |config|
  config.vm.box = "ubuntu/trusty64"
  config.vm.synced_folder pathOfFoodsharing, "/home/vagrant/foodsharing"
  config.vm.network(:forwarded_port, guest: 18080, host: 18080)
  config.vm.provision :shell, inline: "apt-get update"
  config.vm.provision :docker
  config.vm.provision "shell",
    inline: "/home/vagrant/foodsharing/scripts/start", run: 'always'
end
