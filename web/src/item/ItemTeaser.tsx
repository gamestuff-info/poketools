import {useContext} from 'react';
import AppContext, {AppContextProps} from '../common/Context';
import Teaser from '../search/Teaser';
import {generatePath} from 'react-router-dom';
import {Routes} from '../routes';
import PktMarkdown from '../common/components/PktMarkdown';
import {AssetPackage, getAssetUrl} from '../common/getAssetUrl';

export default function ItemTeaser(props: { item: ApiRecord.Item.ItemInVersionGroup }) {
    const {item} = props;
    const {currentVersion} = useContext(AppContext) as Required<AppContextProps>;
    const image = item.icon ? (<img src={getAssetUrl(`/item/${item.icon}`, AssetPackage.MEDIA)} alt=""/>) : undefined;

    return (
        <Teaser label={item.name}
                image={image}
                href={generatePath(Routes.ITEM_VIEW, {version: currentVersion.slug, item: item.slug})}
                description={<PktMarkdown>{item.shortDescription ?? ''}</PktMarkdown>}
        />
    );
}
