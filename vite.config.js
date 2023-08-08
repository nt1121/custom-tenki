import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import sassGlobImports from 'vite-plugin-sass-glob-import';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/scss/app.scss', 'resources/js/app.js', 'resources/js/app_weather.js'],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    // Vueプラグインは、Single File Componentsで
                    // 参照する場合、アセットのURLをLaravelのWebサーバを
                    // 指すように書き換えます。
                    // これを`null`に設定すると、Laravelプラグインは
                    // アセットURLをViteサーバを指すように書き換えます。
                    base: null,

                    // Vueプラグインは、絶対URLを解析し、ディスク上のファイルへの
                    // 絶対パスとして扱います。
                    // これを`false`に設定すると、絶対URLはそのままになり、
                    // 期待通りにpublicディレクトリの中で、アセットへ参照できます。
                    includeAbsolute: false,
                },
            },
        }),
        sassGlobImports()
    ],
});
