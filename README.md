# Netdisco Integration Module for Icinga Web 2 IcingaDB

Integrate [Netdisco](https://netdisco.org/) network discovery data directly into Icinga Web 2. This module adds a **Ports** tab to IcingaDB host views, displaying discovered switch ports, VLAN assignments, connected nodes, and optional MRTG traffic sparklines.

## Features

- **IcingaDB Integration**: Adds "Netdisco Ports" tab to host detail views
- **Port Inventory**: Lists all discovered switch ports with status, speed, and VLANs
- **Node Tracking**: Shows the last connected MAC address, IP, and vendor (OUI lookup)
- **MRTG Sparklines**: Renders inline 24-hour SVG traffic graphs from local MRTG log files

## Requirements

- Icinga Web
- IcingaDB
- Netdisco
- MRTG (optional, for traffic graphs)

## Installation

```bash
cd /usr/share/icingaweb2/modules
git clone https://github.com/bmtwl/icingaweb2-module-netdisco.git netdisco

icingacli module enable netdisco
```

## Configuration

Navigate to **Configuration → Modules → Netdisco → Configuration**.

### Database

| Setting | Description |
|---------|-------------|
| **Database Resource** | Icinga Web resource pointing to your Netdisco PostgreSQL database |
| **Match Field** | Device attribute to match against Icinga host names: `name`, `dns`, or `ip` |

### MRTG Graphs (optional)

| Setting | Description |
|---------|-------------|
| **MRTG Base URL** | Web path to MRTG HTML pages (e.g. `/mrtg`). Makes sparklines clickable. |
| **MRTG Log Path** | Filesystem path to MRTG `.log` files (e.g. `/var/www/mrtg`). Enables sparkline rendering. |
| **MRTG Port Field** | Port attribute used for MRTG file indexing (usually `port`, `name`, or `descr`) |

**Note:** Port identifiers containing slashes (e.g. `1/1/1`) are automatically converted to underscores (`1_1_1`) to match MRTG file naming conventions.

## Permissions

- `netdisco/show` — View Netdisco data in IcingaDB host tabs
- `netdisco/config` — Access module configuration

## How It Works

1. When viewing an IcingaDB host, the module queries Netdisco's `device` table using the configured match field
2. If a matching switch/router is found, a compact device header and a "Netdisco Ports" tab appears
3. The tab displays:
   - All switch ports with real-time status indicators
   - VLAN tagging (native VLANs shown in bold)
   - Last seen node with MAC, IP, DNS, and vendor lookup
4. If MRTG log path is configured, 24-hour SVG sparklines are rendered inline for each port

## Troubleshooting

**"No ports discovered for this device"**
- Verify the Netdisco database resource is correctly configured
- Check that the match field aligns with your Icinga host naming convention
- Ensure Netdisco has successfully discovered the device

**Missing MRTG sparklines**
- Verify the MRTG log path is readable by the web server process
- Confirm log files follow the naming pattern: `{device_ip}_{port_id}.log`
- Check that slashes in port IDs are expected to be converted to underscores

**Tab does not appear**
- Confirm the host object is managed by IcingaDB (doesn't work with legacy Monitoring)
- Verify the `netdisco/show` permission is granted
