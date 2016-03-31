# -*- mode: ruby -*-
# vi: set ft=ruby :

# All Vagrant configuration is done below. The "2" in Vagrant.configure
# configures the configuration version (we support older styles for
# backwards compatibility). Please don't change it unless you know what
# you're doing.
Vagrant.configure(2) do |config|
  # The most common configuration options are documented and commented below.
  # For a complete reference, please see the online documentation at
  # https://docs.vagrantup.com.

  # Every Vagrant development environment requires a box. You can search for
  # boxes at https://atlas.hashicorp.com/search.
  config.vm.box = "puphpet/ubuntu1404-x64"

  # For internet connectivity
  config.vm.provider "virtualbox" do |v|
      v.customize ["modifyvm", :id, "--natdnshostresolver1", "on"]
      v.customize ["modifyvm", :id, "--natdnsproxy1", "on"]
  end






  config.vm.provision :shell, path: "./CI/jdk8-install.sh"
  config.vm.provision :shell, path: "./CI/gremlin-server/install.sh"
  config.vm.provision :shell, path: "./CI/neo4j/install.sh"
  config.vm.provision :shell, path: "./CI/orient/install.sh"




  # Install test databases when building machine
  #config.vm.provision :shell, path: "./myci/provision.sh"

  # Start databases every time
  #config.vm.provision :shell, path: "./myci/startup.sh", run: "always"
end
