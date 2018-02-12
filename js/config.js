const dotenv = require('dotenv');

const config_load_result = dotenv.config();

if (config_load_result.error) {
    throw config_load_result.error;
}

const config = config_load_result.parsed;

// Set up some defaults if properties aren't there
if (!config.hasOwnProperty('SOCKET_PORT')) {
    config.SOCKET_PORT = 8080;
} else {
    config.SOCKET_PORT = parseInt(config.SOCKET_PORT);
}

if (!config.hasOwnProperty('REDIS_HOST')) {
    config.REDIS_HOST = '127.0.0.1';
}

if (!config.hasOwnProperty('REDIS_PORT')) {
    config.REDIS_PORT = 6379;
} else {
    config.REDIS_PORT = parseInt(config.REDIS_PORT);
}

if (!config.hasOwnProperty('REDIS_PASSWORD')) {
    config.REDIS_PASSWORD = null;
}

if (!config.hasOwnProperty('APP_DEBUG')) {
    config.APP_DEBUG = false;
} else {
    config.APP_DEBUG = config.APP_DEBUG.toLowerCase() === "true";
}

module.exports = config;