./vendor/bin/kahlan --no-header --spec=tests

if ! [ -x "$(command -v chokidar)" ]; then
  echo 'Error: chokidar is required to re-run tests on file change. Try npm install -g chokidar-cli' >&2
  exit 1
fi

chokidar "**/*.php" -c './vendor/bin/kahlan --no-header --spec=tests'
