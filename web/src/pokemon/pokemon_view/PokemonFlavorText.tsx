import React, {useContext, useMemo} from 'react';
import AppContext, {AppContextProps} from '../../common/Context';

export default function PokemonFlavorText(props: { pokemon: ApiRecord.Pokemon.Pokemon.PokemonView }) {
    const {pokemon} = props;
    const {currentVersion} = useContext(AppContext) as Required<AppContextProps>;

    return useMemo(() => {
        for (const flavorText of pokemon.flavorText) {
            if (flavorText.version === currentVersion['@id']) {
                if (flavorText.flavorText) {
                    return (<span className="pkt-pokemon-view-flavortext">{flavorText.flavorText}</span>);
                }
                return null;
            }
        }
        return null;
    }, [pokemon, currentVersion]);
}
