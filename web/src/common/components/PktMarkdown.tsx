import React from 'react';
import MathJax from 'mathjax3-react';
import JsxParser from 'react-jsx-parser';
import loadComponent from '../loadComponent';
import {Loadable} from 'loadable-components';

// These are included as loadable components in Markdown text.  Because TypeScript can't check types in dynamically
//  loaded content, these must be cast to Loadable<any> to work.
const PokemonEvolvesWithItemTable = loadComponent(() => import('../../pokemon/PokemonEvolvesWithItemTable')) as Loadable<any>;

interface PktMarkdownPropsBase {
    children: string
}

type PktMarkdownProps = PktMarkdownPropsBase & Record<string, any>

export default function PktMarkdown(props: PktMarkdownProps) {
    const className = ['pkt-text'];
    if (props.className) {
        className.concat(props.className.split(' '));
    }

    const jsx = `<MathJax.Html html={${JSON.stringify(props.children)}}/>`;

    return (
        <>
            <JsxParser jsx={jsx}
                       className={className.join(' ')}
                       autoCloseVoidElements
                       components={{
                           MathJax,
                           PokemonEvolvesWithItemTable,
                       }}
            />
        </>
    );
}
