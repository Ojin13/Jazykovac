# WordPress-MultiLanguage-Mapping-Synchronizer
WordPress cross-domain post/page/taxonomy/WooCommerce synchronization tool for multi-language websites.

# What is it
This code was created for mapping WordPress content IDs in one database (DB_mapping.sql) shared by multiple WordPress installations on different subdomains and this way connecting same content (eg.: WooCommerce product or post) with different languages. 

# Examples

<b>Mapping whole website:</b><br>
You have a website "www.example.com" and you sell phones. Your website is completly done in english and now you want to translate it to the French. So you create new WordPress instalation on subdomain "www.fr.example.com" and copy your english version there. You translated every product and now you have to connect english and french versions of the same products together so you can switch between languages in just one click. You could do it manually but if you have too many products/posts/pages etc it would take ages. Because whole website is just translated copy of english version, all IDs are same, so this code just save them in database and with links like www.example.com?p=ID (?p=ID redirect you to post with given ID) you can switch between languages in your menu. <b>That's easy part.</b><br>
If you didn't copy english content to french installation, this synchronizer would map whole english content, save it's IDs to database and recreate the same content on french installation, while mapping newly created content (that is now in Draft status waiting to be translated) and at the same time, connecting new content to the english version in the databse, so you can swith between them in one click.
<br><br><b>Creating content:</b><br>
Everything you do in one installation happens also in every other installation.
Eg.:
You create new post/page/product in english installation. This post/page/product is also automatically created in French installation, already connected to english version and set to "Draft mode", waiting to be translated.
<br>Code synchronize actions like:
- Create
- Move to trash
- Untrash
- Delete


# Taxonomy
Synchronizer is also mapping WordPress taxonomy so you can switch between categories and tags. Every newly created content, that was mapped, keeps it's categories and tags. Categories also keeps its relationships(parent/child category)

Synchronizer can map this content:
- Posts
- Pages
- Tags
- Categories
- WooCommerce products
- WooCommerce tags
- WooCommerce categories
