import axios, {AxiosRequestConfig, AxiosResponse} from 'axios';
import {setupCache} from 'axios-cache-adapter';

const apiHost = process.env.REACT_APP_API_HOST as string;
if (!apiHost) {
    throw Error('API host not available');
}
const responseCache = setupCache({
    // Use cache expiration from response
    readHeaders: true,
});
const client = axios.create({
    adapter: responseCache.adapter,
    baseURL: apiHost,
    headers: {Accept: 'application/ld+json'},
});

/**
 * Make an absolute URL with an endpoint
 * @param endpoint
 */
function makeAbsolute(endpoint: string) {
    const url = new URL(apiHost);
    url.pathname = (url.pathname + endpoint).replaceAll('//', '');
    return url.toString();
}

/**
 * Make a request to the server.
 *
 * @template DataT The received data type
 * @param endpoint
 * @param params
 * @param useVersion
 * @param options
 */
export function pktQuery<DataT>(endpoint: string, params: Record<string, any> = {}, useVersion?: ApiRecord.Version, options: Partial<AxiosRequestConfig> = {}): Promise<AxiosResponse<DataT>> {
    const useParams = Object.assign({}, params);
    if (useVersion) {
        useParams['_useVersion'] = useVersion.id;
    }
    if (endpoint.startsWith('/')) {
        // Likely an entity IRI
        endpoint = makeAbsolute(endpoint);
    } else {
        endpoint = '/api/' + endpoint;
    }
    return client.request<DataT>(Object.assign({}, {
        method: 'get',
        url: endpoint,
        params: useParams,
    }, options));
}
