Vagrant.configure(2) do |config|

#  config.vm.box = "bento/debian-12"

# Carga da VBOX localmente
  config.vm.box = "livro-caixa"
#  config.vm.box_url = "L:/vbox/bento-debian-12-docker.box"
  config.vm.box_url = "/media/llemes/NVME_1TB/vbox/bento-debian-12-docker.box"

  config.vm.box_download_insecure = true

  config.ssh.username = 'vagrant'
  config.ssh.password = 'vagrant'

  config.vm.define "livro-caixa.local"
  config.vm.hostname = "livro-caixa.local"
  
  config.vm.provider "virtualbox" do |vb|
    vb.customize ["modifyvm", :id, "--natdnshostresolver1", "on"]
    vb.customize ["modifyvm", :id, "--natdnsproxy1", "on"]
    vb.customize ["modifyvm", :id, "--memory", "2048"]
    vb.customize ["modifyvm", :id, "--name", "livro-caixa"]
    vb.gui = false
  end
  
  # Configuração do diretório local onde deverá estar disponibilizado os códigos-fontes do SEI (sei, sip, infra_php, infra_css, infra_js)
  config.vm.synced_folder ".", "/opt/docker", mount_options: ["dmode=777", "fmode=777"]

  # Configuração do redirecionamento entre Máquina Virtual e Host
  config.vm.network :forwarded_port, guest: 8080, host: 8080 # Porta da aplicação

  config.vm.provision "shell", inline: <<-SHELL
     apt update
     apt -y upgrade
     apt install -y vim curl nano htop lynx uidmap dos2unix ca-certificates gnupg lsb-release net-tools

  SHELL

  config.vm.post_up_message = <<-EOF

=========================================================================
  INICIALIZAÇÃO DO AMBIENTE DE DESENVOLVIMENTO FINALIZADA COM SUCESSO ! 
=========================================================================

= Endereços de Acesso à Aplicação ========================================
Memcache ............................... http://localhost:11211

= Comandos Úteis =========================================================
vagrant up                        - Inicializar ambiente do SEI
vagrant halt                      - Desligar ambiente
vagrant destroy                   - Destruir ambiente e base de testes
vagrant ssh                       - Acessar máquina virtual
vagrant status                    - Verificar situação atual do ambiente

EOF
end

# sudo docker run --rm busybox nslookup google.com