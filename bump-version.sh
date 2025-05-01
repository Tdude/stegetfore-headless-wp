#!/bin/bash

THEME_CSS="wp-content/themes/stegetfore-headless-wp/style.css"

# --- GET CURRENT VERSION ---
CUR_VERSION=$(grep -m1 -Eo 'Version: *[0-9]+\.[0-9]+\.[0-9]+' "$THEME_CSS" | grep -Eo '[0-9]+\.[0-9]+\.[0-9]+')

if [ -z "$CUR_VERSION" ]; then
  echo "Could not determine current version!"
  exit 1
fi

# --- BUMP PATCH ---
IFS='.' read -r MAJOR MINOR PATCH <<< "$CUR_VERSION"
PATCH=$((PATCH + 1))
NEW_VERSION="$MAJOR.$MINOR.$PATCH"

echo "Bumping version: $CUR_VERSION → $NEW_VERSION"

# --- UPDATE style.css ---
sed -i '' -E "s/Version: *[0-9]+\.[0-9]+\.[0-9]+/Version: $NEW_VERSION/" "$THEME_CSS"
git add "$THEME_CSS"
echo "Theme version bumped to $NEW_VERSION and staged for commit."