import wpPrettierConfig from '@wordpress/prettier-config';

/**
 * @type {import("prettier").Config}
 */
const config = {
	...wpPrettierConfig,
	printWidth: 120,
};

export default config;
