/**
 * Thrown when an asset cannot be found
 */
class AssetError extends Error {
}

/**
 * Asset packages
 */
export enum AssetPackage {
    MEDIA = 1,
}

/**
 * Get the URL for an asset
 * @param path relative to package root
 * @param pkg package name, or null for default package
 * @returns {string}
 */
export function getAssetUrl(path: string, pkg: AssetPackage): string {
    switch (pkg) {
        case AssetPackage.MEDIA:
            return 'https://static.poketools.gamestuff.info/media/' + path;
    }
    throw new AssetError(`Package "${pkg}" does not exist.`);
}
