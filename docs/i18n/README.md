# README translations

[English documentation](../../README.md)

The main README is the English source. Each translation covers the same sections, commands, file mapping and examples; these are full guides, not shortened introductions.

## Available languages

| Language | Guide | Selection rationale |
| --- | --- | --- |
| English | [README.md](../../README.md) | Shared source for the international project. |
| Русский | [README.ru.md](README.ru.md) | Russia is among the largest respondent groups in the PHP survey below. |
| Қазақша | [README.kk.md](README.kk.md) | Added at the maintainer's request; this edition uses Kazakh Cyrillic. |
| 简体中文 | [README.zh-CN.md](README.zh-CN.md) | China appears prominently in both the PHP survey and GitHub's community data; this edition uses Simplified Chinese. |
| Español | [README.es.md](README.es.md) | Yii has a Spanish guide; one translation serves Spanish-speaking readers across countries. |
| Português (Brasil) | [README.pt-BR.md](README.pt-BR.md) | Brazil is among GitHub's largest developer communities; Yii also publishes a Brazilian Portuguese guide. |
| Deutsch | [README.de.md](README.de.md) | Germany is among GitHub's largest developer communities. |
| Français | [README.fr.md](README.fr.md) | France is among the largest respondent groups in the PHP survey and appears in GitHub's top community list. |
| 日本語 | [README.ja.md](README.ja.md) | Japan appears prominently in both the PHP survey and GitHub's community data. |
| Bahasa Indonesia | [README.id.md](README.id.md) | Indonesia is among GitHub's largest developer communities; Yii has an Indonesian guide. |

## Basis for the selection

Selection reviewed on **2026-10-29** using these primary sources:

- [GitHub Octoverse 2026](https://github.blog/news-insights/octoverse/octoverse-commits-pushes-reach-new-highs-in-2026-as-developers-build-with-agents/) reports large developer communities in China, Brazil, Japan, Indonesia, Germany, Russia and France, alongside the United States, India and United Kingdom.
- [JetBrains: The State of PHP 2025](https://blog.jetbrains.com/phpstorm/2025/10/state-of-php-2025/) describes 1,720 respondents who use PHP as their main language. Japan, the United States, Russia, China and France are its largest country groups. This is a survey sample, not a census of PHP developers.
- Yii's existing [Spanish](https://www.yiiframework.com/doc/guide/2.0/es), [Brazilian Portuguese](https://www.yiiframework.com/doc/guide/2.0/pt-br) and [Indonesian](https://www.yiiframework.com/doc/guide/2.0/id) guides provide additional framework-specific evidence for those editions.

This is an editorial choice based on community indicators, not a measured ranking of this repository's readers. Country counts do not establish preferred reading languages. Repository audience analytics were not used. English remains the shared edition; additional languages can be proposed when readers request them.

## Keeping translations consistent

1. Update the main English README and all affected translations in the same change.
2. Keep explicit section IDs, command names, mapping keys, class names and executable examples unchanged. Code comments and example data stay in English so code blocks can be compared exactly.
3. Use relative links to the repository and provide a language selector in every edition.
4. Run `composer test:docs` (or `php tests/docs.php`) before submitting documentation changes. It checks local links, section parity, code-block parity, CLI coverage and generated-file mappings against the source.
5. Review translated prose for meaning. Automated checks cannot certify linguistic quality; native-speaker reviews and corrections are welcome.

The documentation check is also part of `composer test:all`. Translation work does not change generated PHP identifiers, template labels or the application's interface language.
