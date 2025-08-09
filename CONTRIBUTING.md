# Contributing Guide

Thank you for considering contributing to Aether Console!

We welcome all contributions that help improve this framework-agnostic, CLI helper.

## 📋 Requirements

- PHP 8.3 or higher
- Composer
- Pest (for testing)
- PHPStan
- Laravel Pint


## Steps

1. Fork the repo.
2. Create a new branch (use a meaningful name):
```bash
git checkout -b feature/my-feature
```
3. Make your changes (follow SOLID, DRY, and clean code principles).
4. Run tests and linters:
```bash
composer test
composer analyse
composer lint

```
5. Commit with a clear message:
```bash
git commit -m "feat(registry): improve registry"
```
6. Push and open a Pull Request.
   Include a description of your changes and why they improve the package.


## 🛠 Project Setup

```bash
git clone https://github.com/arielespinoza07/aether.git
cd aether
composer install
```

## 💡 Guidelines
* Stick to SOLID per PR.
* Tests are mandatory for new features.
* Be ready to discuss trade-offs if your implementation differs from the project vision.
* All config arrays should be replaced with strongly typed value objects.


