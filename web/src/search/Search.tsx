import {useParams} from 'react-router-dom';
import {RouteParams} from '../routes';
import {ChangeEvent, useCallback, useContext, useEffect, useReducer} from 'react';
import {Col, Form, Row} from 'react-bootstrap';
import Loading from '../common/components/Loading';
import AppContext, {AppContextProps} from '../common/Context';
import {pktQuery} from '../common/client';
import {AxiosError} from 'axios';
import {FlashSeverity} from '../common/components/Flashes';
import {debounce} from 'lodash';
import loadComponent from '../common/loadComponent';

const AbilityTeaser = loadComponent(() => import('../ability/AbilityTeaser'));
const ItemTeaser = loadComponent(() => import('../item/ItemTeaser'));
const LocationTeaser = loadComponent(() => import('../location/LocationTeaser'));
const MoveTeaser = loadComponent(() => import('../move/MoveTeaser'));
const NatureTeaser = loadComponent(() => import('../nature/NatureTeaser'));
const PokemonTeaser = loadComponent(() => import('../pokemon/PokemonTeaser'));
const TypeTeaser = loadComponent(() => import('../type/TypeTeaser'));

interface SearchState {
    query: string
}

export default function Search(props: {}) {
    const routeParams = useParams<RouteParams.Search>();
    const [state, setState] = useReducer((state: SearchState, newState: Partial<SearchState>) => ({...state, ...newState}), {
        query: routeParams.query ?? '',
    } as SearchState);

    return (
        <>
            <h1>Search</h1>
            <Form>
                <Form.Group as={Row}>
                    <Form.Label column sm={1}>Query</Form.Label>
                    <Col>
                        <Form.Control type="search"
                                      value={state.query}
                                      onChange={(e: ChangeEvent<HTMLInputElement>) => setState({query: e.target.value})}
                        />
                    </Col>
                </Form.Group>
            </Form>
            <Results query={state.query}/>
        </>
    );
}

interface ResultsState {
    loading: boolean
    results: Array<ApiRecord.Search.SearchResult>
    /** Used to avoid showing "no results" on initial load. */
    firstResultSetLoaded: boolean
}

function Results(props: { query: string }) {
    const {currentVersion, setFlashes} = useContext(AppContext) as Required<AppContextProps>;
    const {query} = props;
    const [state, setState] = useReducer((state: ResultsState, newState: Partial<ResultsState>) => ({...state, ...newState}), {
        loading: false,
        results: [],
        firstResultSetLoaded: false,
    } as ResultsState);
    const {results} = state;

    const fetchResults = useCallback(debounce((query: string, useVersion: ApiRecord.Version) => {
        if (query.length === 0) {
            setState({results: []});
            return;
        }
        setState({loading: true});
        pktQuery<ApiRecord.HydraCollection<ApiRecord.Search.SearchResult>>('search_results', {
            q: query,
            method: 'search',
            version: useVersion.id,
            groups: ['search_result'],
        }, useVersion).then(response => {
            setState({
                results: response.data['hydra:member'],
                loading: false,
                firstResultSetLoaded: true,
            });
        }).catch((error: AxiosError) => {
            console.log(error.message);
            setFlashes([{severity: FlashSeverity.DANGER, message: 'Error loading search results.'}]);
            setState({loading: false});
        });
    }, 500), [setFlashes]);

    useEffect(() => {
        if (!currentVersion) {
            return;
        }
        fetchResults(query, currentVersion);
    }, [currentVersion, query, fetchResults]);

    return (
        <>
            {state.loading && <Loading/>}
            {state.firstResultSetLoaded && results.length === 0 && query.length > 0 && (
                <p>No results found.</p>
            )}
            {results.map(result => (
                <ResultTeaser key={`${result.type}-${result.id}`} result={result}/>
            ))}
        </>
    );
}

function ResultTeaser(props: { result: ApiRecord.Search.SearchResult }) {
    const {result} = props;

    switch (result.type) {
        case 'pokemon':
            return <PokemonTeaser pokemon={result.result}/>;
        case 'move':
            return <MoveTeaser move={result.result}/>;
        case 'type':
            return <TypeTeaser type={result.result}/>;
        case 'item':
            return <ItemTeaser item={result.result}/>;
        case 'location':
            return <LocationTeaser location={result.result}/>;
        case 'nature':
            return <NatureTeaser nature={result.result}/>;
        case 'ability':
            return <AbilityTeaser ability={result.result}/>;
    }
}
