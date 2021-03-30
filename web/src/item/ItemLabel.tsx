import EntityLabel from '../common/components/EntityLabel';
import {AssetPackage, getAssetUrl} from '../common/getAssetUrl';
import React, {useContext} from 'react';
import AppContext, {AppContextProps} from '../common/Context';
import {generatePath, Link} from 'react-router-dom';
import {Routes} from '../routes';

interface ItemLabelProps extends Record<string, any> {
    item: Pick<ApiRecord.Item.ItemInVersionGroup, 'name' | 'slug' | 'icon'>
    nolink?: boolean
}

export default function ItemLabel(props: ItemLabelProps) {
    const {item} = props;
    const noLink = props.nolink ?? false;
    const {currentVersion} = useContext(AppContext) as Required<AppContextProps>;

    const elementProps: Record<string, any> = Object.assign({}, props);
    delete elementProps.item;
    delete elementProps.noLink;

    const label = (
        <EntityLabel>
            {item.icon && <EntityLabel.Icon src={getAssetUrl(`/item/${item.icon}`, AssetPackage.MEDIA)}/>}
            <EntityLabel.Text>{item.name}</EntityLabel.Text>
        </EntityLabel>
    );
    if (!noLink) {
        return (
            <Link to={generatePath(Routes.ITEM_VIEW, {
                version: currentVersion.slug,
                item: item.slug,
            })}>
                {label}
            </Link>
        );
    }
    return label;
}
