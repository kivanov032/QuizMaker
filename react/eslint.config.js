// import js from "@eslint/js";
// import globals from "globals";
// import pluginReact from "eslint-plugin-react";
// import json from "@eslint/json";
// import css from "@eslint/css";
// import { defineConfig } from "eslint/config";
//
//
// export default defineConfig([
//   { files: ["**/*.{js,mjs,cjs,jsx}"], plugins: { js }, extends: ["js/recommended"] },
//   { files: ["**/*.{js,mjs,cjs,jsx}"], languageOptions: { globals: globals.browser } },
//   pluginReact.configs.flat.recommended,
//   { files: ["**/*.json"], plugins: { json }, language: "json/json", extends: ["json/recommended"] },
//   { files: ["**/*.css"], plugins: { css }, language: "css/css", extends: ["css/recommended"] },
// ]);




// import js from "@eslint/js";
// import globals from "globals";
// import pluginReact from "eslint-plugin-react";
// import jsonPlugin from "@eslint/json";
// import { defineConfig } from "eslint/config";
//
// const reactRecommended = pluginReact.configs.recommended;
//
// export default defineConfig([
//   {
//     files: ["**/*.{js,mjs,cjs,jsx}"],
//     languageOptions: {
//       globals: globals.browser,
//       parserOptions: {
//         ecmaFeatures: {
//           jsx: true,
//         },
//       },
//     },
//     plugins: {
//       react: pluginReact,
//     },
//     rules: {
//       ...reactRecommended.rules,
//       "react/react-in-jsx-scope": "off",
//     },
//     ...(reactRecommended.settings ? { settings: reactRecommended.settings } : {}),
//     extends: [js.configs.recommended],
//   },
//   {
//     files: ["**/*.json"],
//     ...jsonPlugin.configs.recommended,
//   },
// ]);

import { defineConfig } from 'eslint-define-config';
import js from '@eslint/js';
import globals from 'globals';
import pluginReact from 'eslint-plugin-react';
import jsonPlugin from '@eslint/json';
import pluginReactRefresh from 'eslint-plugin-react-refresh';
import pluginPrettier from 'eslint-plugin-prettier';

const reactRecommended = pluginReact.configs.recommended;

export default defineConfig([
    {
        files: ['**/*.{js,mjs,cjs,jsx}'],
        languageOptions: {
            globals: globals.browser,
            parserOptions: {
                ecmaFeatures: {
                    jsx: true,
                },
            },
        },
        plugins: {
            react: pluginReact,
            'react-refresh': pluginReactRefresh,
            prettier: pluginPrettier,
        },
        rules: {
            ...reactRecommended.rules,
            'react/react-in-jsx-scope': 'off',
            'react-refresh/only-export-components': 'off',
            'prettier/prettier': 'warn',
        },
        settings: {
            ...reactRecommended.settings,
            react: {
                version: 'detect',
            },
        },
    },
    {
        files: ['**/*.json'],
        ...jsonPlugin.configs.recommended,
    },
]);
