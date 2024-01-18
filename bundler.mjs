import esbuild from 'rollup-plugin-esbuild';
import path from 'path';
import { rollup } from 'rollup';
import { nodeResolve } from '@rollup/plugin-node-resolve';
import fs from 'fs/promises';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const MODULE_NAME = path.basename(__dirname);

(async () => {
  /** @type {import('rollup').RollupBuild | undefined} */
  let bundle;

  try {
    await fs.rm(path.resolve(`./install/packages/${MODULE_NAME}`, 'es'), {
      recursive: true,
      force: true,
    });

    await fs.rm(path.resolve(`./install/packages/${MODULE_NAME}`, 'cjs'), {
      recursive: true,
      force: true,
    });

    await fs.rm(path.resolve(`./install/packages/${MODULE_NAME}`, 'iife'), {
      recursive: true,
      force: true,
    });
  } catch (_) {}

  /** @type {import('rollup').RollupOptions} */
  let inputOptions = {
    input: path.resolve(`./install/packages/${MODULE_NAME}/ts/index.ts`),
    plugins: [
      nodeResolve({ extensions: ['.ts'] }),
      esbuild({
        sourceMap: true,
        target: 'esnext',
        exclude: ['./types', './es', './cjs', './iife'],
      }),
    ],
  };

  /** @type {import('rollup').OutputOptions[]} */
  const outputs = [
    {
      format: 'es',
      entryFileNames: '[name].js',
      dir: path.resolve(`./install/packages/${MODULE_NAME}`, 'es'),
      preserveModules: true,
      sourcemap: true,
    },
    {
      format: 'cjs',
      entryFileNames: '[name].js',
      dir: path.resolve(`./install/packages/${MODULE_NAME}`, 'cjs'),
      preserveModules: true,
      sourcemap: true,
    },
  ];

  try {
    bundle = await rollup(inputOptions);
    await Promise.all(outputs.map((output) => bundle.write(output)));
  } catch (error) {
    // buildFailed = true;
    console.error(error);
  }

  if (bundle) {
    // closes the bundle
    await bundle.close();
  }

  // IIFE BUILD

  /** @type {Set<string>} */
  let files = new Set();
  /**
   *
   * @param {string} dirPath
   * @param {string} ext
   * @returns Promise<void>
   */
  const walk = async (dirPath, ext) =>
    Promise.all(
      await fs.readdir(dirPath, { withFileTypes: true }).then((entries) =>
        entries.map((entry) => {
          const childPath = path.join(dirPath, entry.name);

          if (entry.isDirectory()) {
            return walk(childPath, ext);
          }

          if (
            entry.isFile() &&
            entry.name.endsWith(ext) &&
            !entry.name.endsWith('.min' + ext)
          ) {
            const fileName = path.basename(childPath, ext);

            const parts = fileName.split('.');

            const last = parts.pop();

            if (last !== 'min') {
              files.add(childPath);
            }
          }
        })
      )
    );

  await walk(`./install/packages/${MODULE_NAME}/ts`, 'index.ts');

  for (let file of files) {
    const inputOptions = {
      input: file,
      plugins: [
        nodeResolve({ extensions: ['.ts'] }),
        esbuild({
          sourceMap: true,
          target: 'esnext',
          exclude: ['./types', './es', './cjs', './iife'],
        }),
      ],
      external: ['welpodron.core'],
    };
    try {
      bundle = await rollup(inputOptions);
      debugger;
      await bundle.write({
        format: 'iife',
        name: 'window.welpodron',
        extend: true,
        file: path.format({
          ...path.parse(file.replace(/ts/, 'iife')),
          base: '',
          ext: 'js',
        }),
        sourcemap: true,
        globals: { 'welpodron.core': 'window.welpodron' },
      });
    } catch (error) {
      // buildFailed = true;
      console.error(error);
    }
    if (bundle) {
      // closes the bundle
      await bundle.close();
    }
  }
})();
