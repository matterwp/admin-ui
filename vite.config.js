import { createRequire } from 'node:module';
import { defineConfig } from 'vite';
import path from 'node:path';

const require = createRequire(import.meta.url);
const root = path.resolve(__dirname);
const outDir = path.resolve(root, 'dist');

export default defineConfig({
	root,
	publicDir: false,
	build: {
		outDir,
		emptyOutDir: true,
		minify: true,
		cssMinify: true,
		rollupOptions: {
			input: {
				'css/admin-ui': path.resolve(root, 'resources/scss/admin-ui.scss'),
				'js/admin-ui': path.resolve(root, 'resources/js/admin-ui.built.js'),
			},
			output: {
				entryFileNames: '[name].js',
				chunkFileNames: 'js/[name].js',
				assetFileNames: '[name][extname]',
			},
		},
	},
	css: {
		postcss: {
			plugins: [
				require('autoprefixer')({ overrideBrowserslist: ['last 6 versions'] }),
			],
		},
	},
});
