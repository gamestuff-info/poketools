import {generatePath, Redirect, useRouteMatch} from 'react-router-dom';
import React from 'react';

/**
 * Create a redirect if the current version is different from the path's.
 *
 * @param currentVersion
 * @return A Redirect JSX element if the redirect should occur, or null if not.
 */
export default function useVersionRedirect(currentVersion: ApiRecord.Version | null) {
    const {params, path} = useRouteMatch<Record<string, any>>();
    if (currentVersion !== null && 'version' in params && params.version !== currentVersion.slug) {
        const newPath = generatePath(path, Object.assign({}, params, {version: currentVersion.slug}));
        return (
            <Redirect push to={newPath}/>
        );
    }
    return null;
}
