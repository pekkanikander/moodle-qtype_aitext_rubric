#!/bin/sh
# Rename the upstream component qtype_aitext to qtype_aitext_rubric.
#
# This fork of marcusgreen/moodle-qtype_aitext is a separate plugin; after
# every upstream merge, run this script from the repository root to re-apply
# the component rename to whatever the merge brought in. Every rule is
# idempotent: running the script on an already-renamed tree changes nothing.
#
# Excluded from the content pass (they legitimately mention the upstream
# name): this tools/ directory, README.md, changelog.md, and the GitHub
# workflows (which reference the upstream repository by name).
set -eu
cd "$(git rev-parse --show-toplevel)"

# --- 1. Content rules over tracked text files. ---------------------------
# R1  frankenstyle component (classes, tables, lang keys, css, functions).
# R2  plugin directory path.
# R3  quoted bare plugin name ('aitext' / "aitext"): get_qtype() calls,
#     backup element names, XML type="aitext" attributes.
# R4  behat table cells | aitext |.
# R5  the .que.<qtype> body class in CSS.
# R6  @subpackage tag.
# R7  edit form file name references.
# R8  test_question_maker helper methods named after the qtype.
# R9  the restore dispatch method process_<element> for the renamed element.
# R10 quoted XML path fragment '/aitext' (restore get_pathfor(); must track
#     the backup element renamed by R3, or restore paths never match).
RULES='
s/qtype_aitext(?!_rubric)/qtype_aitext_rubric/g;
s{question/type/aitext(?!_rubric)}{question/type/aitext_rubric}g;
s/([\x27"])aitext\1/${1}aitext_rubric$1/g;
s/(\|\s*)aitext(\s*\|)/${1}aitext_rubric$2/g;
s/que\.aitext\b/que.aitext_rubric/g;
s/\@subpackage\s+aitext\b/\@subpackage aitext_rubric/g;
s/edit_aitext_form/edit_aitext_rubric_form/g;
s/(make|get)_aitext_question/${1}_aitext_rubric_question/g;
s/process_aitext\b/process_aitext_rubric/g;
s{([\x27"])/aitext(?!_rubric)\1}{${1}/aitext_rubric$1}g;
'

git ls-files -z -- \
    '*.php' '*.js' '*.map' '*.css' '*.xml' '*.json' '*.yml' '*.yaml' \
    '*.feature' '*.mustache' '*.html' '*.md' '*.txt' \
    ':!tools/' ':!README.md' ':!changelog.md' ':!.github/' \
| xargs -0 perl -pi -e "$RULES"

# --- 2. File renames dictated by Moodle naming conventions. ---------------
rename_file() {
    if [ -e "$1" ] && [ ! -e "$2" ]; then
        git mv "$1" "$2"
    fi
}
rename_file edit_aitext_form.php                edit_aitext_rubric_form.php
rename_file lang/en/qtype_aitext.php            lang/en/qtype_aitext_rubric.php
rename_file lang/fi/qtype_aitext.php            lang/fi/qtype_aitext_rubric.php
rename_file lang/de/qtype_aitext.php            lang/de/qtype_aitext_rubric.php
rename_file mobile/qtype_aitext.html            mobile/qtype_aitext_rubric.html
rename_file mobile/qtype_aitext_app.css         mobile/qtype_aitext_rubric_app.css
rename_file backup/moodle2/backup_qtype_aitext_plugin.class.php \
            backup/moodle2/backup_qtype_aitext_rubric_plugin.class.php
rename_file backup/moodle2/restore_qtype_aitext_plugin.class.php \
            backup/moodle2/restore_qtype_aitext_rubric_plugin.class.php

# --- 3. Behat backup fixtures (.mbz = Moodle tgz). ------------------------
# The backup XML inside uses the qtype name both as element names
# (<aitext id=...>, from backup_nested_element) and as bare element text
# (<qtype>aitext</qtype>), so two extra rules apply on top of the content
# rules. Repacked without .ARCHIVE_INDEX; the extractor reads plain tgz.
MBZRULES="$RULES
s/>aitext</>aitext_rubric</g;
s/<aitext(\s)/<aitext_rubric\$1/g;
s/<aitext>/<aitext_rubric>/g;
s{</aitext>}{</aitext_rubric>}g;
"
for mbz in tests/fixtures/*.mbz; do
    [ -e "$mbz" ] || continue
    tmp=$(mktemp -d)
    tar -xzf "$mbz" -C "$tmp"
    before=$(find "$tmp" -name '*.xml' | sort | xargs cat | shasum)
    find "$tmp" -name '*.xml' -print0 | xargs -0 perl -pi -e "$MBZRULES"
    after=$(find "$tmp" -name '*.xml' | sort | xargs cat | shasum)
    if [ "$before" != "$after" ]; then
        rm -f "$tmp/.ARCHIVE_INDEX"
        (cd "$tmp" && tar -czf - -- *) > "$mbz"
    fi
    rm -rf "$tmp"
done

echo "rename applied"
