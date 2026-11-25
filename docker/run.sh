#!/bin/bash

echo 'Excluir maquinas ... fase 1'
sudo docker ps -a | awk '{ print $14 }' | xargs sudo docker rm $0
echo 'Excluir maquinas ... fase 2'
sudo docker ps -a | awk '{ print $15 }' | xargs sudo docker rm $0

echo 'Monta os containers...'
#sudo docker-compose down --volumes
#sudo docker-compose up -d --build
sudo docker-compose up -d

#Sincroniza o codigo fonte.
sudo docker exec -it app_db sh -c "/opt/docker/docker/grants_livro_caixa.sql"

echo 'Modulos habilitados...'
#sudo docker exec -it php-apache php -m | grep -E 'memcache|oci8|uploadprogress|mcrypt'

sudo docker ps

# Limpa as imagens que nao tem container associado.
# sudo docker image prune
# sudo docker image prune -a

# Atualização do COMPOSER e suas dependencias.
# composer update --with-all-dependencies

# max virtual memory areas vm.max_map_count [65530] is too low, increase to at least [262144]
#sudo sysctl -w vm.max_map_count=262144
#sudo sysctl --system

# Gere um TOKEN da app no SONAR

#sudo docker run --rm -ti \
#	--network="host" \
#    --tty \
#    --interactive \
#    --volume="$(pwd):/usr/src" \
#    --name="sonar_scanner" \
#	sonarsource/sonar-scanner-cli
#    -X \
#    -Dsonar.projectKey=fas \
#    -Dsonar.sources=. \
#    -Dsonar.host.url=http://localhost:9000 \
#    -Dsonar.login=sqp_c9a6351246b48c302ecbaf4ee5c22d99cd666e8c \
#    -Dsonar.verbose=true \
#    -Dsonar.scm.disabled=true \
#    -Dsonar.exclusions='Vendor/**, app/Vendor/**, build/**, node_modules/**' \
#    -Dsonar.inclusions='**/*.php'

# sudo docker system prune -a
