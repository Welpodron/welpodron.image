const fs = require('fs');
const path = require('path');
const archiver = require('archiver');

const MODULE_NAME = path.basename(__dirname);
const DIRS_TO_COPY = ['lib', 'install'];
const FILES_TO_COPY = ['include.php'];

try {
  fs.mkdirSync('.build');
} catch (error) {
  if (error.code !== 'EEXIST') {
    return console.error(error);
  }
}

const output = fs.createWriteStream(
  path.resolve(`./.build/${MODULE_NAME}.zip`)
);

const archive = archiver('zip', {
  zlib: { level: 9 },
});

output.on('close', () => {
  console.log(
    path.resolve(`./.build/${MODULE_NAME}.zip`) +
      ' : ' +
      archive.pointer() +
      ' байт'
  );
});

archive.on('warning', (err) => {
  if (err.code === 'ENOENT') {
    console.warn(err);
  } else {
    throw err;
  }
});

archive.on('error', (err) => {
  throw err;
});

archive.pipe(output);

DIRS_TO_COPY.forEach((dir) => {
  archive.directory(path.resolve(dir), `${MODULE_NAME}/${dir}`);
});

FILES_TO_COPY.forEach((file) => {
  archive.file(path.resolve(file), {
    name: `${MODULE_NAME}/${file}`,
  });
});

archive.finalize();
