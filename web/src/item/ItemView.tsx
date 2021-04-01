import React, {useContext, useMemo, useReducer} from 'react';
import AppContext, {AppContextProps} from '../common/Context';
import {generatePath, Link, useParams} from 'react-router-dom';
import useVersionRedirect from '../common/components/useVersionRedirect';
import NotFound from '../common/components/NotFound';
import {pktQuery} from '../common/client';
import {AxiosError} from 'axios';
import {FlashSeverity} from '../common/components/Flashes';
import setPageTitle from '../common/setPageTitle';
import {Breadcrumb, Col, Row, Table} from 'react-bootstrap';
import {Routes} from '../routes';
import Loading from '../common/components/Loading';
import PktMarkdown from '../common/components/PktMarkdown';
import './ItemView.scss';
import InfoList from '../common/components/InfoList';
import FlagList from '../common/components/FlagList';
import ItemLabel from './ItemLabel';
import {unit} from 'mathjs';
import TypeLabel from '../type/TypeLabel';
import PokemonLabel from '../pokemon/PokemonLabel';
import RadialGauge from '../common/components/gauge/RadialGauge';

interface ItemViewState {
    item?: ApiRecord.Item.ItemInVersionGroup.ItemView | null
    loadingItem: boolean
}

export default function ItemView(props: {}) {
    // Setup
    const {currentVersion, setFlashes} = useContext(AppContext) as Required<AppContextProps>;
    const {item: itemSlug, version: versionSlug} = useParams<{ version: string, item: string }>();
    const [state, setState] = useReducer((state: ItemViewState, newState: Partial<ItemViewState>) => ({...state, ...newState}), {
        loadingItem: false,
    } as ItemViewState);
    const {item} = state;

    // Version redirect
    let redirect;
    if ((redirect = useVersionRedirect(currentVersion))) {
        return redirect;
    }

    // Reset
    if (item && (itemSlug !== item.slug || currentVersion.versionGroup !== item.versionGroup)) {
        setState({item: undefined});
    }

    // Load
    if (item === null) {
        return (<NotFound/>);
    } else if (!state.loadingItem && item === undefined) {
        pktQuery<ApiRecord.HydraCollection<ApiRecord.Item.ItemInVersionGroup.ItemView>>('item_in_version_groups', {
            versionGroup: currentVersion.versionGroup,
            slug: itemSlug,
            page: 1,
            itemsPerPage: 1,
            groups: ['item_view'],
        }, currentVersion).then((response) => {
            if (response.data['hydra:member'].length === 0) {
                setState({item: null, loadingItem: false});
            } else {
                setState({item: response.data['hydra:member'][0], loadingItem: false});
            }
        }).catch((error: AxiosError) => {
            console.log(error.message);
            setFlashes([{severity: FlashSeverity.DANGER, message: 'Error loading Item.'}]);
        });
        setState({loadingItem: true});
    } else if (item) {
        setPageTitle(['Items', item.name]);
    }

    return (
        <div>
            <Breadcrumb>
                <Breadcrumb.Item linkAs="span">{currentVersion.name}</Breadcrumb.Item>
                <Breadcrumb.Item linkAs={Link}
                                 linkProps={{to: generatePath(Routes.ITEM_INDEX, {version: currentVersion.slug})}}>
                    Items
                </Breadcrumb.Item>
                <Breadcrumb.Item active>
                    {!item && <Loading uncontained/>}
                    {item && item.name}
                </Breadcrumb.Item>
            </Breadcrumb>

            {!item && <Loading/>}
            {item && (
                <div>
                    <h1><ItemLabel item={item} nolink/></h1>
                    {item.category && (
                        <p className="pkt-item-view-categories">
                            Categories: {item.category.name}
                        </p>
                    )}
                    <p className={`pkt-flavortext pkt-flavortext-${versionSlug}`}>{item.flavorText}</p>

                    <Row>
                        {/* Stats */}
                        <Col md>
                            <h2>Stats</h2>
                            <ItemStats item={item}/>
                        </Col>

                        {/* Flags */}
                        <Col md>
                            <h2>Flags</h2>
                            <ItemFlags item={item}/>
                        </Col>

                        {/* Berry, if applicable */}
                        {item.berry && (
                            <Col md>
                                <h2>Berry</h2>
                                <ItemBerry berry={item.berry}/>
                            </Col>
                        )}
                    </Row>

                    <h2>Shops</h2>
                    <ItemInShops item={item}/>

                    <h2>Description</h2>
                    <PktMarkdown>
                        {item.description}
                    </PktMarkdown>

                </div>
            )}
        </div>
    );
}

function ItemStats(props: { item: ApiRecord.Item.ItemInVersionGroup.ItemView }) {
    const {item} = props;

    return (
        <InfoList>
            <InfoList.Item name="Buy">
                {item.buy && (<span className="pkt-text">${item.buy}</span>)}
                {!item.buy && 'Cannot be purchased.'}
            </InfoList.Item>
            <InfoList.Item name="Sell">
                {item.sell && (<span className="pkt-text">${item.sell}</span>)}
                {!item.sell && 'Cannot be sold.'}
            </InfoList.Item>
            {item.flingEffect && (
                <InfoList.Item name="Fling">
                    <InfoList>
                        <InfoList.Item name="Power">{item.flingPower ?? ''}</InfoList.Item>
                        <InfoList.Item name="Effect">
                            <PktMarkdown>{item.flingEffect.description}</PktMarkdown>
                        </InfoList.Item>
                    </InfoList>
                </InfoList.Item>
            )}
            <InfoList.Item name="Held By">
                <WildHeldItems item={item}/>
            </InfoList.Item>
        </InfoList>
    );
}

interface WildHeldItemsState {
    loadedForItem?: number
    wildHeldItems: Array<ApiRecord.Pokemon.PokemonWildHeldItem.ItemView>,
    loadingWildHeldItems: boolean,
}

function WildHeldItems(props: { item: ApiRecord.Item.ItemInVersionGroup.ItemView }) {
    const {item} = props;
    const {currentVersion, setFlashes} = useContext(AppContext) as Required<AppContextProps>;
    const [state, setState] = useReducer((state: WildHeldItemsState, newState: Partial<WildHeldItemsState>) => ({...state, ...newState}), {
        loadingWildHeldItems: false,
    } as WildHeldItemsState);
    const {wildHeldItems} = state;

    // Reset
    if (state.loadedForItem !== undefined && state.loadedForItem !== item.id) {
        setState({wildHeldItems: undefined, loadedForItem: undefined});
    }

    // Load
    if (!state.loadingWildHeldItems && wildHeldItems === undefined) {
        pktQuery<ApiRecord.HydraCollection<ApiRecord.Pokemon.PokemonWildHeldItem.ItemView>>(`item_in_version_groups/${item.id}/pokemon_holds_in_wilds`, {
            groups: ['item_view'],
            version: currentVersion.id,
            pagination: 0,
            'order[pokemon.species.position]': 'ASC',
            'order[pokemon.position]': 'ASC',
            'order[rate]': 'ASC',
        }, currentVersion).then(response => {
            setState({
                wildHeldItems: response.data['hydra:member'],
                loadedForItem: item.id,
                loadingWildHeldItems: false,
            });
        }).catch((error: AxiosError) => {
            console.log(error.message);
            setFlashes([{severity: FlashSeverity.DANGER, message: 'Error loading wild Pokémon.'}]);
        });
        setState({loadingWildHeldItems: true});
    }

    return (
        <div>
            {state.loadingWildHeldItems && <Loading uncontained/>}
            {wildHeldItems && wildHeldItems.length === 0 && 'None'}
            {wildHeldItems && wildHeldItems.length > 0 && (
                <InfoList className="pkt-item-view-held">
                    {wildHeldItems.map(wildHeldItem => (
                        <InfoList.Item key={wildHeldItem['@id']} name={<PokemonLabel pokemon={wildHeldItem.pokemon}/>}>
                            <RadialGauge value={wildHeldItem.rate}/>
                        </InfoList.Item>
                    ))}
                </InfoList>
            )}
        </div>
    );
}

function ItemFlags(props: { item: ApiRecord.Item.ItemInVersionGroup.ItemView }) {
    const {item} = props;

    if (item.flags.length > 0) {
        return (<FlagList flags={item.flags}/>);
    } else {
        return (<p>No special flags apply to this item.</p>);
    }
}

function ItemBerry(props: { berry: ApiRecord.Item.Berry }) {
    const {berry} = props;
    const {sizeMillimeters, growthTimeSeconds} = berry;
    const size = useMemo(() => {
        const measurement = unit(sizeMillimeters, 'mm');
        return [
            measurement.format({notation: 'fixed', precision: 0}),
            measurement.to('in').format({notation: 'fixed', precision: 2}),
        ].join(' / ');
    }, [sizeMillimeters]);
    const growthTime = useMemo(() => {
        const measurement = unit(growthTimeSeconds, 'seconds');
        if (growthTimeSeconds < 60) {
            return measurement.toString();
        } else if (growthTimeSeconds < 3600) {
            return measurement.to('minutes').toString();
        } else if (growthTimeSeconds < 86400) {
            return measurement.to('hours').toString();
        } else {
            return measurement.to('days').toString();
        }
    }, [growthTimeSeconds]);

    return (
        <InfoList>
            <InfoList.Item name="Firmness">{berry.firmness.name}</InfoList.Item>
            <InfoList.Item name="Size">{size}</InfoList.Item>
            <InfoList.Item name="Harvest">{berry.harvest}</InfoList.Item>
            <InfoList.Item name="Growth Time">{growthTime}</InfoList.Item>
            {berry.water !== undefined && <InfoList.Item name="Water">{berry.water}</InfoList.Item>}
            {berry.weeds !== undefined && <InfoList.Item name="Weeds">{berry.weeds}</InfoList.Item>}
            {berry.pests !== undefined && <InfoList.Item name="Pests">{berry.pests}</InfoList.Item>}
            {berry.smoothness !== undefined && <InfoList.Item name="Smoothness">{berry.smoothness}</InfoList.Item>}
            {berry.naturalGiftType && (
                <InfoList.Item name="Natural Gift">
                    <InfoList>
                        <InfoList.Item name="Type"><TypeLabel type={berry.naturalGiftType}/></InfoList.Item>
                        <InfoList.Item name="Power">{berry.naturalGiftPower ?? ''}</InfoList.Item>
                    </InfoList>
                </InfoList.Item>
            )}
            {berry.flavors.length > 0 && (
                <InfoList.Item name="Flavors">
                    <InfoList>
                        {berry.flavors.map(flavor => (
                            <InfoList.Item key={flavor.flavor['@id']} name={flavor.flavor.name}>
                                {flavor.level}
                            </InfoList.Item>
                        ))}
                    </InfoList>
                </InfoList.Item>
            )}
        </InfoList>
    );
}

interface ItemInShopsState {
    loadedForItem?: number
    shopItems?: Array<ApiRecord.Item.ShopItem.ItemView> | null
    loadingShopItems: boolean
}

function ItemInShops(props: { item: ApiRecord.Item.ItemInVersionGroup.ItemView }) {
    const {item} = props;
    const {currentVersion, setFlashes} = useContext(AppContext) as Required<AppContextProps>;
    const [state, setState] = useReducer((state: ItemInShopsState, newState: Partial<ItemInShopsState>) => ({...state, ...newState}), {
        loadingShopItems: false,
    } as ItemInShopsState);
    const {shopItems} = state;

    // Reset
    if (state.loadedForItem !== undefined && state.loadedForItem !== item.id) {
        setState({shopItems: undefined, loadedForItem: undefined});
    }

    // Load
    if (!state.loadingShopItems && shopItems === undefined) {
        pktQuery<ApiRecord.HydraCollection<ApiRecord.Item.ShopItem.ItemView>>(`item_in_version_groups/${item.id}/in_shops`, {
            groups: ['item_view'],
            pagination: 0,
        }, currentVersion).then(response => {
            setState({
                shopItems: response.data['hydra:member'],
                loadedForItem: item.id,
                loadingShopItems: false,
            });
        }).catch((error: AxiosError) => {
            console.log(error.message);
            setFlashes([{severity: FlashSeverity.DANGER, message: 'Error loading shop items.'}]);
        });
        setState({loadingShopItems: true});
    }

    return (
        <div>
            {!shopItems && <Loading/>}
            {shopItems && shopItems.length === 0 && <p>This item is not sold in shops.</p>}
            {shopItems && shopItems.length > 0 && (
                <Table size="sm">
                    <thead>
                    <tr>
                        <th>Location</th>
                        <th>Shop</th>
                        <th>Price</th>
                    </tr>
                    </thead>
                    <tbody>
                    {shopItems.map(shopItem => (
                        <tr key={shopItem.id}>
                            {/* Location */}
                            <td>
                                <span>
                                    <Link to={generatePath(Routes.LOCATION_VIEW, {
                                        version: currentVersion.slug,
                                        location: getShopLocation(shopItem.shop).slug,
                                    })}>
                                        {getShopLocation(shopItem.shop).name}
                                    </Link>
                                    {!shopItem.shop.locationArea.isDefault && ` (${getShopAreaName(shopItem.shop)})`}
                                </span>
                            </td>

                            {/* Shop */}
                            <td>
                                {shopItem.shop.name}
                            </td>

                            {/* Price */}
                            <td className="pkt-text">
                                {shopItem.buy && `$${shopItem.buy}`}
                                {!shopItem.buy && '–'}
                            </td>
                        </tr>
                    ))}
                    </tbody>
                </Table>
            )}
        </div>
    );
}

function getShopLocation(shop: ApiRecord.Location.Shop.ItemView): ApiRecord.Location.LocationInVersionGroup {
    const {locationArea} = shop;
    if (locationArea.location) {
        return locationArea.location;
    } else if (typeof locationArea.treeRoot === 'object' && locationArea.treeRoot.location) {
        return locationArea.treeRoot.location;
    }
    throw Error('shop location area has no resolvable location.');
}

function getShopAreaName(shop: ApiRecord.Location.Shop.ItemView): string | null {
    if (shop.locationArea.isDefault) {
        return null;
    }
    const areas = [shop.locationArea.name];
    if (typeof shop.locationArea.treeRoot === 'object') {
        areas.unshift(shop.locationArea.treeRoot.name);
    }
    return areas.join(', ');
}
