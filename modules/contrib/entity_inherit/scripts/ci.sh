#!/bin/bash
#
# Run tests, meant to be run on CirlceCI.
#
set -e

echo '=> Run fast tests.'
./scripts/test.sh

echo '=> Deploy a Drupal 9 environment.'
./scripts/deploy.sh

echo '=> End-to-end tests on D9.'
./scripts/end-to-end-tests.sh

echo '=> Tests on Drupal 9 environment.'
./scripts/test-running-environment.sh

echo '=> Destroy the Drupal 9 environment.'
./scripts/destroy.sh
