#!/bin/bash

copy_env_files() {
  project_root="$1"
  cd "${project_root}" || exit
  env_files=(".env" ".env.test")
  for file in "${env_files[@]}"; do
    if [[ ! -f "$file" ]]; then
      cp "$file".example "$file"
    fi
  done
}

setup_database() {
  waitContainer "${DB_SERVICE}"
  docker-compose exec -T "$DB_SERVICE" bash -c "$EXEC_CMD"
  echo -e "${RED} start creating database and migrating schema ${NC}\r\n"
  EXEC_CMD="./bin/console --no-interaction doctrine:database:create --if-not-exists && ./bin/console --no-interaction doctrine:migrations:migrate "
  docker-compose exec -T -u lon "$APP_SERVICE" bash -c "$EXEC_CMD"
  echo -e "${RED} start creating test database and migrating schema ${NC}\r\n"
  EXEC_CMD="./bin/console --no-interaction doctrine:database:create --if-not-exists --env=test && ./bin/console --no-interaction doctrine:migrations:migrate --env=test "
  docker-compose exec -T -u lon "$APP_SERVICE" bash -c "$EXEC_CMD"
}

run_docker_compose() {
  project_root="$1"
  cd "${project_root}" || exit
  docker-compose up -d
}

function getContainerHealth {
  docker inspect --format "{{.State.Health.Status}}" $1
}

function waitContainer {
  while
    STATUS=$(getContainerHealth $1)
    [ "${STATUS}" != "healthy" ]
  do
    if [ "${STATUS}" == "unhealthy" ]; then
      echo "Failed!"
    fi
    printf .
    lf=$'\n'
    sleep 1
  done
  printf "$lf"
}

function docker_stop() {
  if [ "$MACHINE" == "linux" ]; then
    NGINXRUNNING=$(systemctl is-active nginx)
    APACHERUNNING=$(systemctl is-active apache2)
    MYSQLRUNNING=$(systemctl is-active mysql)
    if [ $APACHERUNNING = "active" ]; then
      echo -e "${CYAN}Apache is running, stopping it${NC}"
      sudo service apache2 stop
      echo -e "${YELLOW}Apache2 stopped${NC}"
    fi
    if [ $NGINXRUNNING = "active" ]; then
      echo -e "${PURPLE}nginx is running, stopping it${NC}"
      sudo service nginx stop
      echo "${YELLOW}Nginx stopped${NC}"
    fi
    if [ $MYSQLRUNNING = "active" ]; then
      echo -e "${BLACK}MySql is running, stopping it${NC}"
      sudo service mysql stop
      echo -e "${YELLOW}MySql stopped${NC}"
    fi
  fi
  if [ "$MACHINE" == "mac" ]; then
    NGINXRUNNING=$(ps aux | grep -v grep | grep -c -i ^nginx$)
    APACHERUNNING=$(ps aux | grep -v grep | grep -c -i ^httpd$)
    MYSQLRUNNING=$(ps aux | grep -v grep | grep -c -i ^mysql$)
    if [ $APACHERUNNING != 0 ]; then
      echo -e "${CYAN}Apache is running, stopping it${NC}"
      brew services stop httpd
      echo -e "${YELLOW}Apache2 stopped${NC}"
    fi
    if [ $NGINXRUNNING != 0 ]; then
      echo -e "${PURPLE}nginx is running, stopping it${NC}"
      brew services stop nginx
      echo "${YELLOW}Nginx stopped${NC}"
    fi
    if [ $MYSQLRUNNING != 0 ]; then
      echo -e "${BLACK}MySql is running, stopping it${NC}"
      brew services stop mysql
      echo -e "${YELLOW}MySql stopped${NC}"
    fi
  fi
  echo -e "${YELLOW}Stopping Dockers...${NC}"
  docker stop $(docker ps -a -q)
  echo -e "Docker ${GREEN}Stopped${NC}"
  echo -e "${YELLOW}Stopping Docker-compose...${NC}"
  $COMPOSE stop
  echo -e "Docker-compose ${GREEN}Stopped${NC}"
  echo -e "${YELLOW}Old docker running project stopped ,now you can start lon...${NC}"
}

function print_help_menu() {
  echo -e "${CUSTOM_F_COLOR[4]} -----------------------------------------------------------------------------------------------------------------------${NC}"
  echo -e "${CUSTOM_F_COLOR[4]} |${CUSTOM_F_COLOR[26]}                                   you have these commands for execute                                               ${CUSTOM_F_COLOR[4]}|${NC}"
  echo -e "${CUSTOM_F_COLOR[180]} -----------------------------------------------------------------------------------------------------------------------${NC}"
  echo -e "${CUSTOM_B_COLOR[0]}${CUSTOM_F_COLOR[202]}    help | -h:                                                                                                          ${NC}"
  echo -e "${CUSTOM_B_COLOR[0]}${CUSTOM_F_COLOR[172]}          this commmand show help of lon command                                                                        ${NC}"
  echo -e "${CUSTOM_B_COLOR[0]}${CUSTOM_F_COLOR[196]}             sample : lon  help                                                                                         ${NC}"
  echo -e "${CUSTOM_F_COLOR[180]} -----------------------------------------------------------------------------------------------------------------------${NC}"
  echo -e "${CUSTOM_B_COLOR[232]}${CUSTOM_F_COLOR[184]}    docker-stop | -ds:                                                                                                  ${NC}"
  echo -e "${CUSTOM_B_COLOR[232]}${CUSTOM_F_COLOR[202]}       with this command other project run in docker will stop,and then you can run lon                                 ${NC}"
  echo -e "${CUSTOM_B_COLOR[232]}${CUSTOM_F_COLOR[88]}          sample : lon docker-stop | lon -ds                                                                            ${NC}"
  echo -e "${CUSTOM_F_COLOR[180]} -----------------------------------------------------------------------------------------------------------------------${NC}"
  echo -e "${CUSTOM_B_COLOR[233]}${CUSTOM_F_COLOR[104]}   up:                                                                                                                  ${NC}"
  echo -e "${CUSTOM_B_COLOR[233]}${CUSTOM_F_COLOR[140]}      lon up use for running docker-compose up commands                                                                 ${NC}"
  echo -e "${CUSTOM_B_COLOR[233]}${CUSTOM_F_COLOR[160]}         sample : lon up                                                                                                ${NC}"
  echo -e "${CUSTOM_F_COLOR[180]} -----------------------------------------------------------------------------------------------------------------------${NC}"
  echo -e "${CUSTOM_B_COLOR[232]}${CUSTOM_F_COLOR[32]}   down:                                                                                                                ${NC}"
  echo -e "${CUSTOM_B_COLOR[232]}${CUSTOM_F_COLOR[68]}       lon down use for running docker-compose down commands                                                            ${NC}"
  echo -e "${CUSTOM_B_COLOR[232]}${CUSTOM_F_COLOR[160]}           sample : lon down                                                                                            ${NC}"
  echo -e "${CUSTOM_F_COLOR[180]} -----------------------------------------------------------------------------------------------------------------------${NC}"
  echo -e "${CUSTOM_B_COLOR[16]}${CUSTOM_F_COLOR[104]}    php:                                                                                                                ${NC}"
  echo -e "${CUSTOM_B_COLOR[16]}${CUSTOM_F_COLOR[140]}       lon php use for proxy php commands to your web container service                                                 ${NC}"
  echo -e "${CUSTOM_B_COLOR[16]}${CUSTOM_F_COLOR[160]}            sample : lon php -v                                                                                         ${NC}"
  echo -e "${CUSTOM_F_COLOR[180]} -----------------------------------------------------------------------------------------------------------------------${NC}"
  echo -e "${CUSTOM_B_COLOR[16]}${CUSTOM_F_COLOR[104]}    test:                                                                                                               ${NC}"
  echo -e "${CUSTOM_B_COLOR[16]}${CUSTOM_F_COLOR[140]}       lon test use for proxy test commands to your web container service                                               ${NC}"
  echo -e "${CUSTOM_B_COLOR[16]}${CUSTOM_F_COLOR[160]}            sample : lon test                                                                                           ${NC}"
  echo -e "${CUSTOM_F_COLOR[180]} -----------------------------------------------------------------------------------------------------------------------${NC}"
  echo -e "${CUSTOM_B_COLOR[233]}${CUSTOM_F_COLOR[104]}    bin:                                                                                                                ${NC}"
  echo -e "${CUSTOM_B_COLOR[233]}${CUSTOM_F_COLOR[140]}      lon bin use for proxy vendor binary commands on the web container                                                 ${NC}"
  echo -e "${CUSTOM_B_COLOR[233]}${CUSTOM_F_COLOR[160]}           sample : lon bin php-parse                                                                                   ${NC}"
  echo -e "${CUSTOM_F_COLOR[180]} -----------------------------------------------------------------------------------------------------------------------${NC}"
  echo -e "${CUSTOM_B_COLOR[232]}${CUSTOM_F_COLOR[32]}    composer:                                                                                                           ${NC}"
  echo -e "${CUSTOM_B_COLOR[232]}${CUSTOM_F_COLOR[68]}       lon composer use for proxy composer commands to your web container service                                       ${NC}"
  echo -e "${CUSTOM_B_COLOR[232]}${CUSTOM_F_COLOR[160]}           sample : lon composer -v                                                                                     ${NC}"
  echo -e "${CUSTOM_F_COLOR[180]} -----------------------------------------------------------------------------------------------------------------------${NC}"
  echo -e "${CUSTOM_B_COLOR[232]}${CUSTOM_F_COLOR[32]}   mysql | mariadb:                                                                                                     ${NC}"
  echo -e "${CUSTOM_B_COLOR[232]}${CUSTOM_F_COLOR[68]}      lon mysql use for initiate a mysql CLI terminal session within the 'db' container                                 ${NC}"
  echo -e "${CUSTOM_B_COLOR[232]}${CUSTOM_F_COLOR[160]}          sample: lon mysql                                                                                             ${NC}"
  echo -e "${CUSTOM_B_COLOR[232]}${CUSTOM_F_COLOR[56]}              Enter your database name: ${CYAN}logs${CUSTOM_B_COLOR[232]}${CUSTOM_F_COLOR[56]}                                                                            ${NC}"
  echo -e "${CUSTOM_F_COLOR[180]} -----------------------------------------------------------------------------------------------------------------------${NC}"
  echo -e "${CUSTOM_B_COLOR[232]}${CUSTOM_F_COLOR[184]}  mysql-bash | mariadb-bash:                                                                                            ${NC}"
  echo -e "${CUSTOM_B_COLOR[232]}${CUSTOM_F_COLOR[202]}     lon mysql-bash | mariadb-bash  use for get bash terminal session from the 'db' container                           ${NC}"
  echo -e "${CUSTOM_B_COLOR[232]}${CUSTOM_F_COLOR[88]}           sample: lon mysql-bash                                                                                       ${NC}"
  echo -e "${CUSTOM_F_COLOR[180]} -----------------------------------------------------------------------------------------------------------------------${NC}"
  echo -e "${CUSTOM_B_COLOR[233]}${CUSTOM_F_COLOR[104]}  shell|bash:                                                                                                           ${NC}"
  echo -e "${CUSTOM_B_COLOR[233]}${CUSTOM_F_COLOR[140]}     lon shell use for initiate a bash shell within the 'web' container                                                 ${NC}"
  echo -e "${CUSTOM_B_COLOR[233]}${CUSTOM_F_COLOR[160]}          sample : lon shell                                                                                            ${NC}"
  echo -e "${CUSTOM_F_COLOR[180]} -----------------------------------------------------------------------------------------------------------------------${NC}"
  echo -e "${CUSTOM_B_COLOR[232]}${CUSTOM_F_COLOR[32]}   console | -c:                                                                                                        ${NC}"
  echo -e "${CUSTOM_B_COLOR[232]}${CUSTOM_F_COLOR[68]}      lon console use for proxy commands to ./bin/console of symfony and execute them there                             ${NC}"
  echo -e "${CUSTOM_B_COLOR[232]}${CUSTOM_F_COLOR[160]}          sample: lon console                                                                                           ${NC}"
  echo -e "${CUSTOM_F_COLOR[180]} -----------------------------------------------------------------------------------------------------------------------${NC}"
  echo -e "${CUSTOM_B_COLOR[16]}${CUSTOM_F_COLOR[104]}   redis:                                                                                                               ${NC}"
  echo -e "${CUSTOM_B_COLOR[16]}${CUSTOM_F_COLOR[140]}      lon redis use for Initiate a Redis CLI terminal session within the 'redis' container                              ${NC}"
  echo -e "${CUSTOM_B_COLOR[16]}${CUSTOM_F_COLOR[160]}           sample : lon redis                                                                                           ${NC}"
  echo -e "${CUSTOM_F_COLOR[180]} -----------------------------------------------------------------------------------------------------------------------${NC}"
  echo -e "${CUSTOM_B_COLOR[233]}${CUSTOM_F_COLOR[104]}  any other composer commands :                                                                                         ${NC}"
  echo -e "${CUSTOM_B_COLOR[233]}${CUSTOM_F_COLOR[140]}     Pass unknown commands to the 'docker-compose' binary                                                               ${NC}"
  echo -e "${CUSTOM_B_COLOR[233]}${CUSTOM_F_COLOR[160]}          sample : lon  ls                                                                                              ${NC}"
  echo -e "${CUSTOM_F_COLOR[180]} -----------------------------------------------------------------------------------------------------------------------${NC}"
}

function lon_is_not_running() {
  echo -e "${WHITE}lon service(s) is not running.${NC}" >&2
  echo -e "${WHITE}You may run lon service(s) using the following commands:${NC} './lon up|lon up' or './lon up -d|lon up -d'" >&2
  exit 1
}

function compy_env_files() {
  echo -e "${CUSTOM_F_COLOR[4]} start copy env files from existing .examples files ${NC}\r\n"
  copy_env_files ${PROJECT_ROOT}
  echo -e "${RED} start delete old lon files from /usr/local/bin directory ${NC}\r\n"
  sudo rm -rf ${lon} && sudo rm -rf ${lon} || exit
}

function create_symlink_for_lon_bash() {
  sudo chmod +x lon
  sudo ln -s "${CURRENT_DIR}/lon" /usr/local/bin/lon
  echo -e "${GREEN} lon add to /usr/bin directory successfully ${NC}"
}

function create_necessary_folder() {
  if [ ! -d "${VENDOR_ROOT}" ]; then
    mkdir "${VENDOR_ROOT}"
  fi
  if [ ! -d "${VAR_ROOT}" ]; then
    mkdir "${VAR_ROOT}"
  fi
}

function set_permissions() {
  echo -e "${BLUE} start to set vendor directory permission${NC}"
  sudo chown -R "${WWWUSER}":"${WWWGROUP}" "${VENDOR_ROOT}" && sudo chmod -R 777 "${VENDOR_ROOT}"
  echo -e "${BLUE} permission set for var directory ${NC}\r\n"
  sudo chown -R "${WWWUSER}":"${WWWGROUP}" "${VAR_ROOT}" && sudo chmod -R 777 "${VAR_ROOT}"
  echo -e "${RED} permission set successfully ${NC}\r\n"
}

function proxy_to_php() {
  if [ "$EXEC" == "yes" ]; then
    # shellcheck disable=SC2124
    EXEC_CMD="cd /var/www/html && php $@"
    $COMPOSE exec -T -u lon "$APP_SERVICE" bash -c "$EXEC_CMD"
  else
    lon_is_not_running
  fi
}

function run_phpunit_test() {
  if [ "$EXEC" == "yes" ]; then
    # shellcheck disable=SC2124
    EXEC_CMD="cd /var/www/html && ./bin/phpunit $@"
    $COMPOSE exec -T -u lon "$APP_SERVICE" bash -c "$EXEC_CMD"
  else
    lon_is_not_running
  fi
}

function proxy_to_vendor_bin() {
  if [ "$EXEC" == "yes" ]; then
    # shellcheck disable=SC2124
    EXEC_CMD="cd /var/www/html/vendor/bin $@"
    $COMPOSE exec -u lon "$APP_SERVICE" bash -c "$EXEC_CMD"
  else
    lon_is_not_running
  fi
}

function proxy_to_composer_service() {
  if [ "$EXEC" == "yes" ]; then
    EXEC_CMD="composer $@"
    $COMPOSE exec -u lon "$APP_SERVICE" bash -c "$EXEC_CMD"
  else
    lon_is_not_running
  fi
}

function proxy_to_db_service() {
  if [ "$EXEC" == "yes" ]; then
    echo -e -n "${PURPLE}Enter your database name: ${NC}"
    echo -e -n "${CYAN}"
    read DBNAME
    echo -e -n "${NC}"
    $COMPOSE exec -T "${DB_SERVICE}" bash -c "mysql  -u$DB_USER -p$DB_PASSWORD $DBNAME"
  else
    lon_is_not_running
  fi
}

function proxy_to_db_service_bash() {
  if [ "$EXEC" == "yes" ]; then
    echo -e -n "${CYAN}"
    $COMPOSE exec -T "${DB_SERVICE}" bash
  else
    lon_is_not_running
  fi
}

function proxy_to_web_service_bash() {
  if [ "$EXEC" == "yes" ]; then
    $COMPOSE exec -u lon "$APP_SERVICE" bash
  else
    lon_is_not_running
  fi
}

function proxy_to_symfony_console() {
  if [ "$EXEC" == "yes" ]; then
    EXEC_CMD="./bin/console $@"
    $COMPOSE exec -u lon "$APP_SERVICE" bash -c "$EXEC_CMD"
  else
    lon_is_not_running
  fi
}

function proxy_to_redis_cli() {
  if [ "$EXEC" == "yes" ]; then
    $COMPOSE exec "$REDIS_SERVICE" redis-cli
  else
    lon_is_not_running
  fi
}

function run_docker_compose() {
  docker-compose up -d
}
