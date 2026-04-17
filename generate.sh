#!/bin/bash
# Regenerate eBay Media API client from OpenAPI spec
# Requires: brew install openapi-generator

set -e

SPEC_URL="https://developer.ebay.com/api-docs/master/commerce/media/openapi/3/commerce_media_v1_beta_oas3.json"
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
OUT_DIR="$SCRIPT_DIR/src/Generated"
TMP_DIR="$SCRIPT_DIR/tmp-generated"

echo "Generating eBay Media API client..."

rm -rf "$TMP_DIR" "$OUT_DIR"
mkdir -p "$OUT_DIR"

openapi-generator generate \
  -i "$SPEC_URL" \
  -g php \
  -o "$TMP_DIR" \
  --additional-properties=invokerPackage=Zislogic\\Ebay\\Api\\Media\\Generated,variableNamingConvention=camelCase,composerPackageName=zislogic/ebay-api-media,developerOrganization=Zislogic,srcBasePath=src

cp -r "$TMP_DIR/src/"* "$OUT_DIR/"
rm -rf "$TMP_DIR"

# Fix: openapi-generator produces duplicate $contentType parameters in some API files.
echo "Applying \$contentType fix..."
find "$OUT_DIR/Api" -name "*.php" -exec sed -i '' 's/, \$contentType, / , /g' {} \;
find "$OUT_DIR/Api" -name "*.php" -exec sed -i '' 's/(\$contentType, /(/g' {} \;

echo "Done. Generated files in $OUT_DIR"
