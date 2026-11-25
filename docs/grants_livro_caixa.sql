-- Allow the user to connect specifically from the container's IP
-- Modify instruction 'user'@'*' to 'user'@'your_app_ip'
CREATE USER IF NOT EXISTS 'user'@'*' IDENTIFIED BY 'password';
GRANT ALL PRIVILEGES ON livro_caixa.* TO 'user'@'*';

-- ALTERNATIVE: Allow the user to connect from ANY IP (Common for Docker setups)
CREATE USER IF NOT EXISTS 'user'@'%' IDENTIFIED BY 'password';
GRANT ALL PRIVILEGES ON livro_caixa.* TO 'user'@'%';

-- Apply the changes immediately
FLUSH PRIVILEGES;
