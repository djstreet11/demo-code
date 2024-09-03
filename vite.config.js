import {defineConfig} from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import fs from "fs";
import {resolve} from "path";

export default defineConfig({
	css: {
		devSourcemap: true
	},
	plugins: [
		vue(),
		laravel({
			input: [
				'resources/css/app.scss',
				'resources/js/app.js',
				'resources/css/dashboard.scss',
				'resources/js/dashboard.js',
			],
			refresh: true,
		}),
		{
			name: 'move-build-plugin',
			apply: 'build',
			async generateBundle(options, bundle) {
				// switching to afterBuild
			},
			async writeBundle(options, bundle) {
				const sourceDir = resolve(__dirname, 'public/dist');
				const targetDir = resolve(__dirname, 'public/build');

				// Ensure that the source directory exists
				if (!fs.existsSync(sourceDir)) {
					console.error(`Source directory ${sourceDir} does not exist.`);
					return;
				}

				// Check if target directory exists
				if (!fs.existsSync(targetDir)) {
					// If it doesn't exist, create it
					fs.mkdirSync(targetDir, { recursive: true });
				} else {
					// If it exists, remove all files and subdirectories
					fs.readdirSync(targetDir).forEach(file => {
						const filePath = resolve(targetDir, file);
						fs.rmSync(filePath, { recursive: true, force: true });
					});
				}

				// Move build to target directory
				fs.renameSync(sourceDir, targetDir, (err) => {
					if (err) throw err;
					console.log('Build moved successfully');
				});
			},
		},
	],
	resolve: {
		alias: {
			"@icon": '/resources/images/icons',
			'vue': 'vue/dist/vue.esm-bundler.js'
		},
	},
	build: {
		modulePreload: true,
		cssSourceMap: true,
		sourcemap: true,
		devSourcemap: true ,
		outDir: 'public/dist',
		emptyOutDir: true,
	},
});

