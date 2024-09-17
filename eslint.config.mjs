import path from 'node:path';
import { fileURLToPath } from 'node:url';
import js from '@eslint/js';
import { FlatCompat } from '@eslint/eslintrc';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const compat = new FlatCompat({
	baseDirectory: __dirname,
	recommendedConfig: js.configs.recommended,
	allConfig: js.configs.all,
});

export default [
	...compat.extends('wordpress'),
	{
		languageOptions: {
			ecmaVersion: 11,
			sourceType: 'module',
		},

		rules: {
			camelcase: [
				'error',
				{
					properties: 'never',
				},
			],

			yoda: [
				'error',
				'always',
				{
					onlyEquality: true,
				},
			],

			'space-in-parens': [
				'error',
				'always',
				{
					exceptions: ['empty'],
				},
			],

			eqeqeq: ['error'],
			indent: ['error', 'tab'],
		},
	},
];
