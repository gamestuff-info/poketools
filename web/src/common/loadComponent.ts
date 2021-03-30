import loadable from 'loadable-components';
import Loading from './components/Loading';

/**
 * Wrap loadable with default options
 * @param getComponent
 * @param options
 */
export default function loadComponent<T>(
    getComponent: () => Promise<loadable.DefaultComponent<T>>,
    options: loadable.LoadableOptions<T> = {},
): loadable.Loadable<T> {
    return loadable(getComponent, Object.assign({}, {LoadingComponent: Loading}, options));
}
