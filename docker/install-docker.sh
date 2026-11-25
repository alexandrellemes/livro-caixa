#!/bin/bash

echo 'Step 1- Update your Ubuntu Linux Box.'
sudo apt-get update -y
sudo apt-get install -y curl uidmap dos2unix

sudo apt-get install -y ca-certificates curl gnupg lsb-release

curl -fsSL --insecure https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /usr/share/keyrings/docker-archive-keyring.gpg

# Ubuntu
#echo \
#  "deb [arch=$(dpkg --print-architecture) signed-by=/usr/share/keyrings/docker-archive-keyring.gpg] https://download.docker.com/linux/ubuntu \
#  $(lsb_release -cs) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

# Debian
echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.asc] https://download.docker.com/linux/debian \
     $(lsb_release -cs) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

sudo apt-get update
sudo apt-get install -y docker-ce docker-ce-cli containerd.io

#echo 'Step 3- Add a user to the Docker group.'
#sudo apt-get install -y docker-ce-rootless-extras
#dockerd-rootless-setuptool.sh install

echo 'Step 4 – Check Docker Version'
sudo docker --version
