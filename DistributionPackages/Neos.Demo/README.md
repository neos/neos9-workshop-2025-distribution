# The Neos Demo Package

This is the default site package installed with the Neos base distribution. With this package, you can quickly have
a look into how easy content can be generated and changed.
It also shows the basic concepts of how to build a site package with custom node types and plugins.

## Use exported Neos.Demo content for setting up a new Neos 9 test project

Please follow these steps after `composer install`:

1) `./flow doctrine:migrate`
2) `./flow user:create --roles="Neos.Neos:Administrator" <username> <password> <first name> <last name>`
3) `./flow cr:setup`
4) `./flow cr:import resource://Neos.Demo/Private/Content`
TODO adjust after solving https://github.com/neos/neos-development-collection/issues/4918
Connect to a Neos site 
5) `./flow site:create neosdemo Neos.Demo Neos.Demo:Document.Homepage`

*Hint:* This will probably change in the future as we still have to work on the setup process. 

## About the frontend build stack

We included a frontend build stack based on [Node.js], [NVM], [esbuild], [PostCSS] and [Yarn].

### Installation

Make sure that [Node.js] and [Yarn] are installed. It is recommended to use [NVM] to manage versions of the [Node.js] versions.

```bash
 # Enable the correct nvm
 nvm use
 # Install the package dependencies
 yarn
```

### Commands

| Command         | Description                                       |
| --------------- | ------------------------------------------------- |
| `yarn build`    | Builds all assets                                 |
| `yarn pipeline` | Runs install and then build all assets            |
| `yarn start`    | Watches the sources and rebuilds assets on change |

### Package management

The dependencies are stored in the [`package.json`] file, so if you edit any config, or need new packages, you have to add them to this file. You can read more about this [here](https://nodejs.dev/the-package-json-guide).

### Explanation of the config files

| Filename            | Explantion                                                                                 |
| ------------------- | ------------------------------------------------------------------------------------------ |
| [`.editorconfig`]   | Helps maintain consistent coding styles                                                    |
| [`.eslintignore`]   | These files get ignored from [ESLint]                                                      |
| [`.eslintrc`]       | The configuration file for [ESLint], a pluggable Javascript linter                         |
| [`.nvmrc`]          | This file contains the required [Node.js] version and is used by [NVM]                     |
| [`.postcssrc.js`]   | The configuration for [PostCSS]                                                            |
| [`.prettierignore`] | These files gets excluded from the [Prettier] code formatting                              |
| [`.prettierrc`]     | This is the configuration file for [Prettier]                                              |
| [`.stylelintrc`]    | This is the configuration file for [Stylelint]                                             |
| [`.yarnclean`]      | Cleans and removes unnecessary files from package dependencies                             |
| [`esbuild.mjs`]     | Configuration for [esbuild]                                                                |
| [`yarn.lock`]       | This is the lockfile for [Yarn]. This is needed to get consistent installs across machines |

[node.js]: https://nodejs.org
[nvm]: https://github.com/nvm-sh/nvm#readme
[esbuild]: https://esbuild.github.io
[yarn]: https://yarnpkg.com
[postcss]: https://postcss.org
[eslint]: https://eslint.org
[prettier]: https://prettier.io
[stylelint]: https://stylelint.io
[`package.json`]: package.json
[`.editorconfig`]: .editorconfig
[`.eslintignore`]: .eslintignore
[`.eslintrc`]: .eslintrc
[`.nvmrc`]: .nvmrc
[`.postcssrc.js`]: .postcssrc.js
[`.prettierignore`]: .prettierignore
[`.prettierrc`]: .prettierrc
[`.stylelintrc`]: .stylelintrc
[`.yarnclean`]: .yarnclean
[`esbuild.mjs`]: esbuild.mjs
[`yarn.lock`]: yarn.lock
