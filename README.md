# AI Text Rubric question type

[![Moodle Plugin CI](https://github.com/pekkanikander/moodle-qtype_aitext_rubric/actions/workflows/moodle-ci.yml/badge.svg)](https://github.com/pekkanikander/moodle-qtype_aitext_rubric/actions/workflows/moodle-ci.yml)
[![Moodle Support](https://img.shields.io/badge/Moodle-%3E%3D%204.5-blue)](https://moodle.org)

`qtype_aitext_rubric` is a Moodle question type that accepts free-text
answers and grades them against a **criterion-referenced rubric** with a
Large Language Model. The model never assigns marks: it only selects a
level per criterion and quotes verbatim evidence from the student answer.
The mark is computed in PHP from the validated levels; any deviation from
the expected reply format falls closed to human grading.

**Status: 0.1.0, alpha.** Not yet used in production anywhere.

## Relationship to qtype_aitext

This is a hard fork of Marcus Green's
[moodle-qtype_aitext](https://github.com/marcusgreen/moodle-qtype_aitext),
renamed to a separate component so both plugins can coexist on one site.
All of the upstream feature set (free-form AI prompt and marking scheme,
prompt tester, spellcheck, mobile app support) is retained; upstream is
merged in weekly. Credit for that foundation belongs to Marcus Green and
the upstream contributors, including the ByCS team.

What the fork adds:

- **Rubric grading** — an optional per-question JSON rubric replaces the
  free-form marking scheme. The model's reply is validated strictly
  (exact criterion set, integer levels in range, verbatim evidence
  quotes, length caps); marks are computed in PHP, never by the model.
  Feedback is rendered from a fixed template and opens with a banner
  naming the model. Raw model text is never shown to students.
- **Answer screening** — deterministic prompt-injection tripwires (a
  hard length cap and refusal of the prompt's section-marker sequence)
  route suspect answers to a human grader instead of the model.
- **Display modes** — rubric feedback detail is switchable per question:
  `none`, `coarse` or `fine`.
- **Scaffold-then-fade** — an optional answer skeleton shown above the
  answer box, with a per-quiz override; the grader model never sees it.
- Optional flagging of AI-graded responses for review via the companion
  [local_aitextflags](https://github.com/pekkanikander/moodle-local_aitextflags)
  plugin (rendered only if that plugin is installed).

Much of the fork's code is written by Claude (Anthropic's LLM), as
recorded in the commit trailers; not all of it has been human-reviewed.

## Requirements

- Moodle 4.5 or later.
- Access to the API of an external LLM.
- The two companion question behaviours, installed under
  `question/behaviour/immediate_for_aitext` and
  `question/behaviour/deferred_for_aitext`. Upstream pins both to
  `qtype_aitext` via `is_compatible_question()`, so use these forks,
  which drop that check:
  - [qbehaviour_immediate_for_aitext](https://github.com/pekkanikander/moodle-qbehaviour_immediate_for_aitext)
    ([PR upstream](https://github.com/marcusgreen/moodle-qbehaviour_immediate_for_aitext/pull/1))
  - [qbehaviour_deferred_for_aitext](https://github.com/pekkanikander/moodle-qbehaviour_deferred_for_aitext)
    ([PR upstream](https://github.com/marcusgreen/moodle-qbehaviour_deferred_for_aitext/pull/1))

## Tracking upstream

Upstream is fetched and merged weekly by a scheduled workflow. The
component rename is re-applied to merged code by the idempotent
`tools/rename-from-upstream.sh`.

Changelog: [changelog.md](changelog.md)

## License

Licensed under the [GNU GPL v3 or later](https://www.gnu.org/licenses/gpl-3.0.html).
