# Framework

The Electro framework's subsystems for the standard configuration profiles

### Introduction

This package provides the standard framework subsystems and causes the installation of all the recommended packages for a standard framework configuration.

The standard configuration provides two profiles:
1. The `WebProfile` - the standard functionality for web applications.
1. The `ConsoleProfile` - the standard functionality for console-based applications.

See the framework's documentation to know more about profiles.

##### Subsystems vs Plugins

This package's subsystem modules provide only the main functionality of the framework.

Some optional parts of the framework are available elsewere as plugin packages that you can install on demand. Those will provide you with ORMs, database migrations, templating engines and more.

Plese refer to the framework's documentation for instructions on how to install plugins.

### Installing the framework's standard package bundle

By requiring this package on your project's `composer.json` file, you will install the framework with all recommended packages for a standard configuration.

You should not install this package on an empty project, as it will be missing the underlying files and directory structure required by a fully-working application.

Use the [Electro base installation](https://github.com/electro-framework/electro) as your application's starting point.

### Installing a customized framework profile

In a near future, there will be more framework installation profiles available.

Alternative installation profiles will provide customized versions of the framework that may be more suitable for some scenarios. For instance, a "micro-framework" profile would install a minimum set of subsystems, with a narrower scope and tuned for maximum performance.

For now, this package is the only profile available, which consists of a set of subsystems that forn a generic web framework, suitable for most common usage scenarios.

When alternative profiles became available, you'll be able to find them on GitHub, on the `electro-framework` organization.

## License

The Electro framework is open-source software licensed under the [MIT license](http://opensource.org/licenses/MIT).

**Electro framework** - Copyright © Cláudio Silva and Impactwave, Lda.
