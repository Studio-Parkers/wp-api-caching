# WP API Caching
A plugin that caches API responses into a JSON file and serves the file when the request url matches.

## Table of Contents
- [Requirements](#requirements)
- [Features](#features)
- [Installation/Usage](#installationusage)

## Requirements
- php v8.0+
- Wordpress v6.0.0+

## Features
- Caches API response (of public GET requests) into JSON file with hashed request URI as filename.
- Settings page to select endpoints and relation to those endpoints.
- Automatically clears caches when a related post is updated.

## Installation/Usage
After enabling the plugin there are two new menu items; `WP Cache` and `Settings->API Caching settings`. You'll find an overview of all caches on the `WP Cache` page witht the ability to view and/or delete cache files. On the `Settings->API Caching settings` page you'll find all namespaces and endpoints within each namespace. Each endpoint has a toggle to enable/disable caching for that endpoint. When a toggle is enabled, a new fieldset is shown where you'll be able to create a relation to a post type or even specific posts that will delete the cache when updated.