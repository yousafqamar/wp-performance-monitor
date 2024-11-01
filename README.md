# WordPress Hook Performance Monitor
 is a lightweight PHP tool designed for WordPress developers and site administrators to gain valuable insights into site performance. This single-file plugin tracks the execution time and memory consumption of each WordPress hook, helping to pinpoint performance bottlenecks in plugins, themes, and custom code. With easy setup and comprehensive logging, it enables data-driven optimizations for a faster and more efficient WordPress installation.

## Features

- Monitors execution time of common WordPress hooks
- Tracks memory usage and peak memory consumption
- Logs detailed performance metrics for each hook
- Provides request summary with total execution times
- Debug-mode aware (only runs when WP_DEBUG is enabled)

## Installation

1. Copy the plugin file to your WordPress installation's plugin directory
2. Enable WP_DEBUG in your wp-config.php file
3. Activate the plugin through the WordPress admin interface

## Usage

Once activated, the plugin automatically monitors WordPress hooks and logs their performance metrics to:
```wp-content/hook-performance.log```

The log file includes:
- Individual hook execution times
- Memory usage per hook
- Peak memory usage
- Total request execution time
- Total hook execution time

## Log Format

### Individual Hook Entry
