import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
<<<<<<< HEAD
import tailwindcss from '@tailwindcss/vite';
=======

>>>>>>> eb4d7bce (Agrego archivos del proyecto dentro del repo)

export default defineConfig({
    plugins: [
        laravel({
<<<<<<< HEAD
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
=======
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
});
>>>>>>> eb4d7bce (Agrego archivos del proyecto dentro del repo)
