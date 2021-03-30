const contestFeatures = ['contests', 'super-contests'];

/**
 * Does the given version have Pokemon contests?
 * @param version
 */
export default function versionHasContests(version: ApiRecord.Version) {
    return version.featureSlugs.some(feature => contestFeatures.includes(feature));
}
