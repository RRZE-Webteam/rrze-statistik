const fs = require('node:fs');
const path = require('node:path');

/**
 * Collect license and notice texts for installed production dependencies.
 * The root plugin license is intentionally separate from third-party notices.
 * @returns {void}
 * @throws {Error} If a production dependency has no distributable license text.
 */
function writeLicenseNotices() {
    const root = path.resolve(__dirname, '..');
    const lock = JSON.parse(fs.readFileSync(path.join(root, 'package-lock.json'), 'utf8'));
    const sections = ['Third-party licenses\n\nGenerated from installed production dependencies.\nWordPress supplies jQuery at runtime; its package license is included for completeness.'];
    for (const [relative, entry] of Object.entries(lock.packages).sort(([a], [b]) => a.localeCompare(b))) {
        if (!relative || entry.dev || entry.link) continue;
        const directory = path.join(root, relative);
        if (!fs.existsSync(directory)) {
            if (entry.optional) continue;
            throw new Error(`Dependency is not installed: ${relative}`);
        }
        const metadata = JSON.parse(fs.readFileSync(path.join(directory, 'package.json'), 'utf8'));
        const documents = fs.readdirSync(directory).filter((name) =>
            /^(licen[sc]e|copying|notice)(\.|$)/i.test(name) && fs.statSync(path.join(directory, name)).isFile());
        if (!documents.length) throw new Error(`Missing license text: ${metadata.name}`);
        sections.push(`${metadata.name} ${metadata.version} (${metadata.license || 'see license below'})\n${'='.repeat(72)}\n`
            + documents.sort().map((name) => fs.readFileSync(path.join(directory, name), 'utf8').trim()).join('\n\n'));
    }
    fs.writeFileSync(path.join(root, 'THIRD-PARTY-LICENSES.txt'), `${sections.join('\n\n')}\n`);
}

writeLicenseNotices();
