import React, {useCallback, useContext, useReducer} from 'react';
import {Button, Form} from 'react-bootstrap';
import {AsyncTypeahead, TypeaheadResult} from 'react-bootstrap-typeahead';
import './QuickSearch.scss';
import AppContext, {AppContextProps} from '../common/Context';
import {pktQuery} from '../common/client';
import {AxiosError} from 'axios';
import {FlashSeverity} from '../common/components/Flashes';
import PokemonLabel from '../pokemon/PokemonLabel';
import ItemLabel from '../item/ItemLabel';
import EntityLabel from '../common/components/EntityLabel';
import {generatePath} from 'react-router-dom';
import {Routes} from '../routes';

interface QuickSearchState {
    loading: boolean
    options: Array<ApiRecord.Search.SearchResult>
    query: string
}

export default function QuickSearch(props: {}) {
    const {currentVersion, setFlashes} = useContext(AppContext) as Required<AppContextProps>;
    const [state, setState] = useReducer((state: QuickSearchState, newState: Partial<QuickSearchState>) => ({...state, ...newState}), {
        loading: false,
        options: [],
        query: '',
    } as QuickSearchState);

    const fetchOptions = useCallback((query: string) => {
        if (!currentVersion) {
            return;
        }
        setState({loading: true});
        pktQuery<ApiRecord.HydraCollection<ApiRecord.Search.SearchResult>>('search_results', {
            q: query,
            method: 'autocomplete',
            version: currentVersion.id,
        }, currentVersion).then(response => {
            setState({
                options: response.data['hydra:member'],
                loading: false,
            });
        }).catch((error: AxiosError) => {
            console.log(error.message);
            setFlashes([{severity: FlashSeverity.DANGER, message: 'Error loading search results.'}]);
            setState({loading: false});
        });
    }, [currentVersion, setFlashes]);

    return (
        <>
            {currentVersion && (
                <Form className="pkt-quicksearch" action={generatePath(Routes.SEARCH, {
                    version: currentVersion.slug,
                    query: state.query.length > 0 ? state.query : undefined,
                })}>
                    <AsyncTypeahead id="pkt-quicksearch-input"
                                    className="pkt-quicksearch-input"
                                    isLoading={state.loading}
                                    options={state.options}
                                    onSearch={fetchOptions}
                                    renderMenuItemChildren={((option: TypeaheadResult<ApiRecord.Search.SearchResult>) =>
                                        <Suggestion option={option}/>)}
                                    onInputChange={(query => setState({query: query}))}
                                    onChange={([query]) => setState({query: query.label})}
                                    useCache={false}
                    />
                    <Button variant="outline-info" type="submit">
                        Search
                    </Button>
                </Form>
            )}
        </>
    );
}

function Suggestion(props: { option: ApiRecord.Search.SearchResult }) {
    const {option} = props;

    if (option.type === 'pokemon') {
        return (<PokemonLabel pokemon={option.result} noLink/>);
    } else if (option.type === 'item') {
        return (<ItemLabel item={option.result} nolink/>);
    }

    return (
        <EntityLabel>
            <EntityLabel.Text>{option.result.name}</EntityLabel.Text>
        </EntityLabel>
    );
}
