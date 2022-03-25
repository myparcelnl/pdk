const mainConfig = require('@myparcel/semantic-release-config/composer');
const { addGitHubPlugin, addGitPlugin } = require('@myparcel/semantic-release-config/src/plugins');

module.exports = {
  'extends': '@myparcel/semantic-release-config/composer',
  'plugins': [
    ...mainConfig.plugins,
    addGitHubPlugin(),
    addGitPlugin(),
  ],
};
