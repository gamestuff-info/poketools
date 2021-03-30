import React, {useCallback, useContext, useReducer} from 'react';
import AppContext, {AppContextProps} from '../common/Context';
import useVersionRedirect from '../common/components/useVersionRedirect';
import setPageTitle from '../common/setPageTitle';
import {pktQuery} from '../common/client';
import {AxiosError} from 'axios';
import {FlashSeverity} from '../common/components/Flashes';
import {Breadcrumb, Tab, Tabs} from 'react-bootstrap';
import Loading from '../common/components/Loading';
import {buildOrderParams, QueryCallback} from '../common/components/DataTable';
import ItemTable, {ItemTableRecord} from './ItemTable';

interface ItemIndexState {
    loadedVersionGroup?: string
    pockets?: Array<ApiRecord.Item.ItemPocket> | null
    loadingPockets: boolean
}

export default function ItemIndex(props: {}) {
    const {currentVersion, setFlashes} = useContext(AppContext) as Required<AppContextProps>;
    const [state, setState] = useReducer((state: ItemIndexState, newState: Partial<ItemIndexState>) => ({...state, ...newState}), {
        loadingPockets: false,
    } as ItemIndexState);
    const {pockets} = state;
    setPageTitle('Items');

    // Reset
    if (state.loadedVersionGroup !== undefined && currentVersion.versionGroup !== state.loadedVersionGroup) {
        setState({loadedVersionGroup: undefined, pockets: undefined});
    }

    // Load
    if (!state.loadingPockets && pockets === undefined) {
        pktQuery<ApiRecord.HydraCollection<ApiRecord.Item.ItemPocket>>('item_pocket_in_version_groups', {
            versionGroup: currentVersion.versionGroup,
            pagination: 0,
        }, currentVersion).then((response) => {
            setState({
                pockets: response.data['hydra:member'],
                loadingPockets: false,
                loadedVersionGroup: currentVersion.versionGroup,
            });
        }).catch((error: AxiosError) => {
            console.log(error.message);
            setFlashes([{severity: FlashSeverity.DANGER, message: 'Error loading pockets.'}]);
        });
        setState({loadingPockets: true});
    }

    // Version redirect
    let redirect;
    if ((redirect = useVersionRedirect(currentVersion as ApiRecord.Version))) {
        return redirect;
    }

    return (
        <div>
            <Breadcrumb>
                <Breadcrumb.Item linkAs="span">{(currentVersion as ApiRecord.Version).name}</Breadcrumb.Item>
                <Breadcrumb.Item active>Items</Breadcrumb.Item>
            </Breadcrumb>

            <h1>Items</h1>
            {!pockets && <Loading/>}
            {pockets && (
                <Tabs defaultActiveKey={pockets[0].slug}>
                    {pockets.map(pocket => (
                        <Tab key={pocket.id} eventKey={pocket.slug} title={pocket.name}>
                            <ItemTableForPocket pocket={pocket}/>
                        </Tab>
                    ))}
                </Tabs>
            )}
        </div>
    );
}

function ItemTableForPocket(props: { pocket: ApiRecord.Item.ItemPocket }) {
    const {pocket} = props;
    const {currentVersion} = useContext(AppContext) as Required<AppContextProps>;
    const query: QueryCallback<ItemTableRecord> = useCallback((pageIndex, pageSize, sortBy) => {
        const params = Object.assign({}, {
            versionGroup: currentVersion.versionGroup,
            pocket: pocket.id,
            page: pageIndex + 1,
            itemsPerPage: pageSize,
        }, buildOrderParams(sortBy));
        return pktQuery<ApiRecord.HydraCollection<ApiRecord.Item.ItemInVersionGroup>>('item_in_version_groups', params, currentVersion);
    }, [pocket, currentVersion]);

    return (<ItemTable query={query}/>);
}
