const express = require('express');
const path = require('path');
const WebTorrent = require('webtorrent');

const app = express();
const port = process.env.PORT || 3000;

// Centralized open-source client engine instance
let client = new WebTorrent();

function getClient() {
    if (!client || client.destroyed) {
        client = new WebTorrent();
    }
    return client;
}

// Map frontend presentation directories
app.use(express.static(path.join(__dirname, 'public')));

// Core Stream API Pipeline
app.get('/api/stream', (req, res) => {
    const torrentId = req.query.torrent;
    
    if (!torrentId) {
        return res.status(400).json({ error: 'Missing magnet connection parameters' });
    }

    const wt = getClient();
    let torrent = wt.get(torrentId);

    if (!torrent) {
        try {
            torrent = wt.add(torrentId, { deselect: true });
        } catch (err) {
            return res.status(400).json({ error: 'Invalid Magnet URI structure or infoHash string' });
        }
    }

    if (!torrent.ready) {
        torrent.once('ready', () => evaluateMediaFiles(torrent, res));
    } else {
        evaluateMediaFiles(torrent, res);
    }
});

function evaluateMediaFiles(torrent, res) {
    // Identify target video container configurations
    const file = torrent.files.find(f => f.name.endsWith('.mp4') || f.name.endsWith('.mkv') || f.name.endsWith('.webm'));

    if (!file) {
        return res.status(404).json({ error: 'No streamable video binaries inside target resource metadata' });
    }

    res.setHeader('Content-Disposition', `inline; filename="${file.name}"`);
    res.setHeader('Content-Type', 'video/mp4');

    // Establish sequential piece read priorities and channel the pipeline buffer
    const stream = file.createReadStream();
    stream.pipe(res);

    res.on('close', () => {
        stream.destroy();
    });
}

app.listen(port, () => {
    console.log(`Open Source Streaming Platform running at: http://localhost:${port}`);
});
